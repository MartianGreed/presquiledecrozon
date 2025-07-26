<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Panther\PantherTestCase;

#[Group('e2e')]
final class RegisterTest extends PantherTestCase
{
    use BaseE2ETrait;

    public function testPageIsWorking(): void
    {
        $this->client->request('GET', '/creer-mon-compte');

        // Wait for the page to load
        $this->waitForElement('form');

        $this->assertSelectorIsVisible('form[name="register_user"]');
        $this->assertSelectorIsVisible('input[id="register_user_email"]');
        $this->assertSelectorIsVisible('input[id="register_user_password_first"]');
        $this->assertSelectorIsVisible('input[id="register_user_password_second"]');
        $this->assertSelectorTextContains('h1', 'Créer mon compte');
    }

    public function testSuccessfulRegistration(): void
    {
        $this->client->request('GET', '/creer-mon-compte');

        $uniqueEmail = 'test_' . uniqid() . '@example.com';

        $this->client->submitForm('Je crée mon compte', [
            'register_user[email]' => $uniqueEmail,
            'register_user[password][first]' => 'TestPassword123!',
            'register_user[password][second]' => 'TestPassword123!',
        ]);

        // After successful registration, should redirect to login page
        $this->waitForElement('h1');
        $currentUrl = $this->client->getCurrentURL();
        $this->assertStringEndsWith('/login', $currentUrl, 'Should redirect to login after registration');
    }

    public function testRegistrationWithExistingEmail(): void
    {
        $existingEmail = 'existing_' . uniqid() . '@example.com';

        // First create a user
        $this->createTestUser($existingEmail, 'TestPassword123!');

        // Now try to register with the same email
        $this->client->request('GET', '/creer-mon-compte');
        $this->client->submitForm('Je crée mon compte', [
            'register_user[email]' => $existingEmail,
            'register_user[password][first]' => 'TestPassword123!',
            'register_user[password][second]' => 'TestPassword123!',
        ]);

        // Should stay on registration page with error
        $this->assertStringEndsWith('/creer-mon-compte', $this->client->getCurrentURL());
        $this->assertPageContainsText('Cette valeur est déjà utilisée');
    }

    public function testRegistrationWithPasswordMismatch(): void
    {
        $this->client->request('GET', '/creer-mon-compte');

        $this->client->submitForm('Je crée mon compte', [
            'register_user[email]' => 'test_' . uniqid() . '@example.com',
            'register_user[password][first]' => 'TestPassword123!',
            'register_user[password][second]' => 'DifferentPassword123!',
        ]);

        // Should stay on registration page with error
        $this->assertStringEndsWith('/creer-mon-compte', $this->client->getCurrentURL());
        $this->assertPageContainsText('Les deux mots de passe ne correspondent pas');
    }

    public function testRegistrationWithEmptyFields(): void
    {
        $this->client->request('GET', '/creer-mon-compte');

        $form = $this->client->getCrawler()->selectButton('Je crée mon compte')->form();
        $form['register_user[email]'] = '';
        $form['register_user[password][first]'] = '';
        $form['register_user[password][second]'] = '';
        $this->client->submit($form);

        $this->assertStringEndsWith('/creer-mon-compte', $this->client->getCurrentURL());
    }

    public function testRegistrationWithInvalidEmail(): void
    {
        $this->client->request('GET', '/creer-mon-compte');

        $this->client->submitForm('Je crée mon compte', [
            'register_user[email]' => 'invalid-email',
            'register_user[password][first]' => 'TestPassword123!',
            'register_user[password][second]' => 'TestPassword123!',
        ]);

        $this->assertStringEndsWith('/creer-mon-compte', $this->client->getCurrentURL());
    }
}
