<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use Symfony\Component\Panther\{Client, PantherTestCase};

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

        self::assertSelectorExists('form[name="request_reset_password"]');
        self::assertSelectorExists('input[id="request_reset_password_email"]');
    }
}