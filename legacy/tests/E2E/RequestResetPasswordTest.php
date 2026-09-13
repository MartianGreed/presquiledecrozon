<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Panther\PantherTestCase;

#[Group('e2e')]
final class RequestResetPasswordTest extends PantherTestCase
{
    use BaseE2ETrait;

    public function testPageIsWorking(): void
    {
        $this->client->request('GET', '/reinitialisation-mot-de-passe');

        // Wait for the page to load
        $this->waitForElement('body');

        $this->assertSelectorIsVisible('form');
        $this->assertSelectorWillContain('h1', 'Réinitialiser mon mot de passe');
        $this->assertSelectorWillContain('#request_reset_password_submit', 'Réinitialiser mon mot de passe');
    }
}
