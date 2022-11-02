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

    public function generateRandomToken(string $id, int $length = 20): string
    {

        $prefix = random_bytes(max(1, intval($length / 2)));
        $suffix = random_bytes(max(1, intval($length / 2)));

        return bin2hex($prefix).$this->generateToken($id).bin2hex($suffix);
    }
}
