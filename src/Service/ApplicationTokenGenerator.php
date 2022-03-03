<?php

namespace App\Service;

final class ApplicationTokenGenerator
{
    public function __construct(private readonly string $secret)
    {
    }

    public function generateToken(string $id): string
    {
        return crypt($id, $this->secret);
    }
}
