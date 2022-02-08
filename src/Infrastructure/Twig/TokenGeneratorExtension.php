<?php

namespace App\Infrastructure\Twig;

use App\Service\ApplicationTokenGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class TokenGeneratorExtension extends AbstractExtension
{
    public function __construct(private readonly ApplicationTokenGenerator $applicationTokenGenerator)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('generate_token', [$this->applicationTokenGenerator, 'generateToken'])
        ];
    }
}