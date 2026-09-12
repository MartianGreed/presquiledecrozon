<?php

namespace App\Infrastructure\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class PriceExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('price', [$this, 'displayPrice']),
        ];
    }

    public function displayPrice(int $amount): string
    {
        return sprintf('%d €', number_format($amount, 2, ','));
    }
}
