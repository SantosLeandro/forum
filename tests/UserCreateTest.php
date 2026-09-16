<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserCreateTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->createQuery('DELETE FROM App\Entity\Post')->execute();
        $em->createQuery('DELETE FROM App\Entity\Topic')->execute();
        $em->createQuery('DELETE FROM App\Entity\User')->execute();
    }

    private function csrfToken(): string
    {
        $crawler = $this->client->request('GET', '/user.create');
        return $crawler->filter('input[name="token"]')->attr('value');
    }

    private function firstAvatar(): string
    {
        $crawler = $this->client->request('GET', '/user.create');
        return $crawler->filter('input[name="avatar"]')->first()->attr('value');
    }

    public function testValidSubmitCreatesUserAndRedirectsToLogin(): void
    {
        $this->client->request('POST', '/users', [
            'token' => $this->csrfToken(),
            'email' => 'novo@example.com',
            'username' => 'novo_usuario',
            'password' => 'SenhaForte123',
            'password_confirmation' => 'SenhaForte123',
            'avatar' => $this->firstAvatar(),
        ]);

        $this->assertResponseRedirects('/login');

        $user = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(User::class)
            ->findOneBy(['email' => 'novo@example.com']);
        $this->assertNotNull($user);
        $this->assertTrue($user->isEnabled());
        $this->assertSame('novo_usuario', $user->getUsername());
    }

    public function testSubmitWithoutAvatarShowsMessage(): void
    {
        $this->client->request('POST', '/users', [
            'token' => $this->csrfToken(),
            'email' => 'semavatar@example.com',
            'username' => 'sem_avatar',
            'password' => 'SenhaForte123',
            'password_confirmation' => 'SenhaForte123',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Selecione um avatar', $this->client->getResponse()->getContent());
    }

    public function testSubmitWithInvalidAvatarShowsMessage(): void
    {
        $this->client->request('POST', '/users', [
            'token' => $this->csrfToken(),
            'email' => 'avatarinv@example.com',
            'username' => 'avatar_inv',
            'password' => 'SenhaForte123',
            'password_confirmation' => 'SenhaForte123',
            'avatar' => '../../etc/passwd',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Avatar inválido', $this->client->getResponse()->getContent());
    }

    public function testSubmitWithShortPasswordShowsMessage(): void
    {
        $this->client->request('POST', '/users', [
            'token' => $this->csrfToken(),
            'email' => 'curta@example.com',
            'username' => 'senha_curta',
            'password' => 'abc123',
            'password_confirmation' => 'abc123',
            'avatar' => $this->firstAvatar(),
        ]);

        $this->assertResponseIsSuccessful();

        $user = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(User::class)
            ->findOneBy(['email' => 'curta@example.com']);
        $this->assertNull($user);
    }

    public function testSubmitWithMismatchedPasswordsShowsMessage(): void
    {
        $this->client->request('POST', '/users', [
            'token' => $this->csrfToken(),
            'email' => 'divergente@example.com',
            'username' => 'senhas_divergentes',
            'password' => 'SenhaForte123',
            'password_confirmation' => 'OutraSenha456',
            'avatar' => $this->firstAvatar(),
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('As senhas não coincidem', $this->client->getResponse()->getContent());

        $user = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(User::class)
            ->findOneBy(['email' => 'divergente@example.com']);
        $this->assertNull($user);
    }

    public function testSubmitWithDuplicateEmailShowsMessage(): void
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $existing = new User();
        $existing->setEmail('dup@example.com');
        $existing->setUsername('duplicado');
        $existing->setPassword('não-usado-no-teste');
        $em->persist($existing);
        $em->flush();

        $this->client->request('POST', '/users', [
            'token' => $this->csrfToken(),
            'email' => 'dup@example.com',
            'username' => 'outro_nome',
            'password' => 'SenhaForte123',
            'password_confirmation' => 'SenhaForte123',
            'avatar' => $this->firstAvatar(),
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('já está em uso', $this->client->getResponse()->getContent());
    }
}