<?php

namespace App\Infrastructure\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class StringExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('excerpt', [$this, 'excerpt'])
        ];
    }

    public function excerpt(string $toExcerpt, int $wordsCount): string
    {
        $parts = explode(' ', $toExcerpt);
        $tmp = array_slice($parts, 0, $wordsCount);

        return implode(' ', $tmp) . '...';
    }
}
