<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthLoginTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testLoginPageRendersForm(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.form-panel-header', 'Entrar');
        $this->assertGreaterThanOrEqual(1, $crawler->filter('input[name="_username"]')->count());
        $this->assertGreaterThanOrEqual(1, $crawler->filter('input[name="_password"]')->count());
        $this->assertGreaterThanOrEqual(1, $crawler->filter('input[name="token"]')->count());
        $this->assertGreaterThanOrEqual(1, $crawler->filter('a[href="/user.create"]')->count());
    }

    public function testLoginWithInvalidCsrfShowsMessage(): void
    {
        $this->client->request('POST', '/login', [
            'token' => 'token-invalido',
            '_username' => 'alguem@example.com',
            '_password' => 'SenhaQualquer123',
        ]);

        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Sessão expirada', $this->client->getResponse()->getContent());
    }

    public function testInvalidCredentialsShowsTranslatedErrorAndPreservesUsername(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $token = $crawler->filter('input[name="token"]')->attr('value');

        $this->client->request('POST', '/login', [
            'token' => $token,
            '_username' => 'nao_existe@example.com',
            '_password' => 'SenhaErrada123',
        ]);

        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $content = $this->client->getResponse()->getContent();
        $this->assertStringContainsString('E-mail ou senha inválidos', $content);
        $this->assertStringContainsString('value="nao_existe@example.com"', $content);
    }
}