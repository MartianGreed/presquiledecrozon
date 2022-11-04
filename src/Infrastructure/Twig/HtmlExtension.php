<?php

declare(strict_types=1);

namespace App\Infrastructure\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class HtmlExtension extends AbstractExtension
{
    public function __construct(private readonly RequestStack $requestStack)
    {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_current_route', [$this, 'isCurrentRoute']),
        ];
    }

    public function isCurrentRoute(string $route): string
    {
        $request = $this->requestStack->getMainRequest();

        return $request?->attributes->get('_route') === $route ? 'is-active' : '';
    }
}