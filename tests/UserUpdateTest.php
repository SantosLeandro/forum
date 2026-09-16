<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserUpdateTest extends WebTestCase
{
    private const EMAIL = 'perfil@test.local';
    private const PASS = 'SenhaForte123';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->createQuery('DELETE FROM App\Entity\Post')->execute();
        $em->createQuery('DELETE FROM App\Entity\Topic')->execute();
        $em->createQuery('DELETE FROM App\Entity\User')->execute();

        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user = new User();
        $user->setEmail(self::EMAIL);
        $user->setUsername('perfil_user');
        $user->setPassword($hasher->hashPassword($user, self::PASS));
        $em->persist($user);
        $em->flush();

        $this->login();
    }

    private function login(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $token = $crawler->filter('input[name="token"]')->attr('value');
        $this->client->request('POST', '/login', [
            '_username' => self::EMAIL,
            '_password' => self::PASS,
            'token' => $token,
        ]);
        $this->client->followRedirect();
    }

    private function csrfToken(): string
    {
        $crawler = $this->client->request('GET', '/users');
        return $crawler->filter('input[name="token"]')->attr('value');
    }

    private function firstAvatar(): string
    {
        $crawler = $this->client->request('GET', '/users');
        return $crawler->filter('input[name="avatar"][type="radio"]')->first()->attr('value');
    }

    public function testUpdateWithInvalidAvatarShowsMessage(): void
    {
        $this->client->request('POST', '/users/update', [
            'token' => $this->csrfToken(),
            'email' => self::EMAIL,
            'password' => '',
            'avatar' => '../fora',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Selecione um avatar válido', $this->client->getResponse()->getContent());
    }

    public function testUpdateWithDuplicateEmailShowsMessage(): void
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $other = new User();
        $other->setEmail('ocupado@test.local');
        $other->setUsername('outro_user');
        $other->setPassword($hasher->hashPassword($other, self::PASS));
        $em->persist($other);
        $em->flush();

        $this->client->request('POST', '/users/update', [
            'token' => $this->csrfToken(),
            'email' => 'ocupado@test.local',
            'password' => '',
            'avatar' => $this->firstAvatar(),
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('já está em uso', $this->client->getResponse()->getContent());
    }

    public function testUpdateWithMismatchedPasswordsShowsMessage(): void
    {
        $this->client->request('POST', '/users/update', [
            'token' => $this->csrfToken(),
            'email' => self::EMAIL,
            'password' => 'NovaSenha123',
            'password_confirmation' => 'OutraSenha456',
            'avatar' => $this->firstAvatar(),
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('As senhas não coincidem', $this->client->getResponse()->getContent());

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $user = $em->getRepository(User::class)->findOneBy(['username' => 'perfil_user']);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $this->assertTrue($hasher->isPasswordValid($user, self::PASS));
    }

    public function testValidUpdateRedirectsAndPersists(): void
    {
        $this->client->request('POST', '/users/update', [
            'token' => $this->csrfToken(),
            'email' => 'novoemail@test.local',
            'password' => '',
            'avatar' => $this->firstAvatar(),
        ]);

        $this->assertResponseRedirects('/users');

        $user = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(User::class)
            ->findOneBy(['username' => 'perfil_user']);
        $this->assertSame('novoemail@test.local', $user->getEmail());
    }
}