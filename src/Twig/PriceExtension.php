<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class PriceExtension extends AbstractExtension
{
    public function getFilters()
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