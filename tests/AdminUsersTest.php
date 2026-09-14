<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminUsersTest extends WebTestCase
{
    private const ADMIN_EMAIL = 'admin@test.local';
    private const ADMIN_PASS = 'AdminPass123!';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $em = $this->em();
        $em->createQuery('DELETE FROM App\Entity\Post')->execute();
        $em->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->createUser(self::ADMIN_EMAIL, 'admin', self::ADMIN_PASS, ['ROLE_ADMIN']);
        $this->createUser('leandro@test.local', 'leandro', 'UserPass456!', ['ROLE_USER']);
    }

    private function em(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    private function createUser(string $email, string $username, string $plainPassword, array $roles): User
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setPassword($hasher->hashPassword($user, $plainPassword));
        $user->setRoles($roles);
        $em = $this->em();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    private function login(string $email, string $password): void
    {
        $crawler = $this->client->request('GET', '/login');
        $token = $crawler->filter('input[name="token"]')->attr('value');
        $this->client->request('POST', '/login', [
            '_username' => $email,
            '_password' => $password,
            'token' => $token,
        ]);
        $this->client->followRedirect();
    }

    public function testAnonymousIsRedirectedToLogin(): void
    {
        $this->client->request('GET', '/admin/users');
        $this->assertResponseRedirects('/login');
    }

    public function testAdminCanListUsers(): void
    {
        $this->login(self::ADMIN_EMAIL, self::ADMIN_PASS);

        $crawler = $this->client->request('GET', '/admin/users');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains('Gerenciar usuários');
        $this->assertStringContainsString(self::ADMIN_EMAIL, $crawler->text());
        $this->assertStringContainsString('leandro@test.local', $crawler->text());
    }

    public function testToggleWithoutTokenIsRejected(): void
    {
        $this->login(self::ADMIN_EMAIL, self::ADMIN_PASS);

        $this->client->request('POST', '/admin/users.toggle', ['user_id' => 2, 'page' => 1]);
        $this->assertResponseStatusCodeSame(400);
    }

    public function testAdminCanToggleAnotherUser(): void
    {
        $this->login(self::ADMIN_EMAIL, self::ADMIN_PASS);

        $crawler = $this->client->request('GET', '/admin/users');
        $form = $crawler->filter('form[action="/admin/users.toggle"]')->first();
        $token = $form->filter('input[name="token"]')->attr('value');
        $userId = $form->filter('input[name="user_id"]')->attr('value');

        $this->client->request('POST', '/admin/users.toggle', [
            'user_id' => $userId,
            'page' => 1,
            'token' => $token,
        ]);
        $this->assertResponseRedirects('/admin/users?page=1');
        $this->client->followRedirect();

        $this->assertStringContainsString('Inativo', $this->client->getCrawler()->text());

        $em = $this->em();
        $user = $em->getRepository(User::class)->find((int) $userId);
        $this->assertFalse($user->isEnabled());
    }

    public function testAdminCannotToggleOwnAccount(): void
    {
        $this->login(self::ADMIN_EMAIL, self::ADMIN_PASS);

        $crawler = $this->client->request('GET', '/admin/users');
        $form = $crawler->filter('form[action="/admin/users.toggle"]')->first();
        $token = $form->filter('input[name="token"]')->attr('value');

        $admin = $this->em()->getRepository(User::class)->findOneBy(['email' => self::ADMIN_EMAIL]);

        $this->client->request('POST', '/admin/users.toggle', [
            'user_id' => $admin->getId(),
            'page' => 1,
            'token' => $token,
        ]);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testDeactivatedUserCannotLogIn(): void
    {
        $em = $this->em();
        $leandro = $em->getRepository(User::class)->findOneBy(['email' => 'leandro@test.local']);
        $leandro->setEnabled(false);
        $em->flush();

        $this->client->request('GET', '/login');
        $token = $this->client->getCrawler()->filter('input[name="token"]')->attr('value');
        $this->client->request('POST', '/login', [
            '_username' => 'leandro@test.local',
            '_password' => 'UserPass456!',
            'token' => $token,
        ]);

        $this->assertSame(302, $this->client->getResponse()->getStatusCode());
    }
}