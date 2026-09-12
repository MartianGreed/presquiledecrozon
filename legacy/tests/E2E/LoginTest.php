<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Panther\PantherTestCase;

#[Group('e2e')]
final class LoginTest extends PantherTestCase
{
    use BaseE2ETrait;

    public function testPageIsWorking(): void
    {
        $this->client->request('GET', '/login');

        // Wait for any content to load
        $this->waitForElement('body');

        // Check if we have the form
        $this->assertSelectorIsVisible('form');
        $this->assertSelectorIsVisible('input[id="inputEmail"]');
        $this->assertSelectorIsVisible('input[id="inputPassword"]');
        $this->assertSelectorTextContains('h1', 'Connexion');
    }

    public function testSuccessfulLogin(): void
    {
        $email = 'user_' . uniqid() . '@example.com';
        $password = 'TestPassword123!';

        $this->createTestUser($email, $password);
        $this->login($email, $password);

        $this->assertNotEquals('http://127.0.0.1:9080/login', $this->client->getCurrentURL());
        $this->assertSelectorNotExists('.text-red');
    }

    public function testFailedLoginWithWrongPassword(): void
    {
        $this->client->request('GET', '/login');

        $this->client->submitForm('Je me connecte', [
            'email' => 'nonexistent@example.com',
            'password' => 'WrongPassword123!',
        ]);

        $this->assertStringEndsWith('/login', $this->client->getCurrentURL());
        $this->assertSelectorWillBeVisible('.text-red');
    }

    public function testLoginWithEmptyFields(): void
    {
        $this->client->request('GET', '/login');

        $form = $this->client->getCrawler()->selectButton('Je me connecte')->form();
        $form['email'] = '';
        $form['password'] = '';
        $this->client->submit($form);

        $this->assertStringEndsWith('/login', $this->client->getCurrentURL());
    }

    public function testLoginWithInvalidEmail(): void
    {
        $this->client->request('GET', '/login');

        $this->client->submitForm('Je me connecte', [
            'email' => 'invalid-email',
            'password' => 'TestPassword123!',
        ]);

        $this->assertStringEndsWith('/login', $this->client->getCurrentURL());
    }

    public function testLogoutFunctionality(): void
    {
        $email = 'logout_test_' . uniqid() . '@example.com';
        $password = 'TestPassword123!';

        $this->createTestUser($email, $password);
        $this->login($email, $password);

        $this->client->request('GET', '/logout');

        $this->assertStringEndsWith('/', $this->client->getCurrentURL());
    }

    public function testNavigationLinks(): void
    {
        $this->client->request('GET', '/login');

        $registerLink = $this->client->getCrawler()->selectLink('Je n\'ai pas de compte')->link();
        $this->client->click($registerLink);
        $this->client->waitForElementToContain('h1', 'Créer mon compte');
        $this->assertStringEndsWith('/creer-mon-compte', $this->client->getCurrentURL());

        $this->client->request('GET', '/login');
        $resetPasswordLink = $this->client->getCrawler()->selectLink('J\'ai oublié mon mot de passe')->link();
        $this->client->click($resetPasswordLink);
        $this->client->waitForElementToContain('h1', 'Réinitialiser mon mot de passe');
        $this->assertStringEndsWith('/reinitialisation-mot-de-passe', $this->client->getCurrentURL());
    }
}
