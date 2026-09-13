<?php

namespace App\Infrastructure\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class StringExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('excerpt', [$this, 'excerpt']),
            new TwigFilter('human_filesize', [$this, 'humanFilesize'], [
                'is_safe' => ['html'],
            ]),
            new TwigFilter('str_replace', [$this, 'replace'], [
                'is_sage' => ['html'],
            ]),
        ];
    }

    public function excerpt(string $toExcerpt, int $wordsCount): string
    {
        $parts = explode(' ', $toExcerpt);
        $tmp = array_slice($parts, 0, $wordsCount);

        return implode(' ', $tmp) . '...';
    }

    public function humanFilesize(int $size): string
    {
        $units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $step = 1024;
        $i = 0;
        while (($size / $step) > 0.9) {
            $size /= $step;
            ++$i;
        }

        return '<strong>' . round($size, 2) . '</strong> ' . $units[$i];
    }

    public function replace(string $subject, string $search, string $replacement): string
    {
        return str_replace($search, $replacement, $subject);
    }
}
