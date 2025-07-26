<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

#[Group('e2e')]
final class RequestResetPasswordTest extends PantherTestCase
{
    private Client $client;

    public function setUp(): void
    {
        $this->client = static::createPantherClient();
    }

    public function testPageIsWorking(): void
    {
        $this->client->request('GET', '/reinitialisation-mot-de-passe');

        $this->assertSelectorIsVisible('form[name="request_reset_password"]');
        $this->assertSelectorIsVisible('input[id="request_reset_password_email"]');
    }
}
