<?php

namespace App\Util;

final class FilePathUtil
{
    /**
     * @param non-empty-string $separator
     * @return list<string>
     */
    public static function splitPath(string $path, string $separator = '/'): array
    {
        return explode($separator, $path);
    }

    public static function getParentDirectory(string $path): string
    {
        $parts = self::splitPath($path);
        array_pop($parts);

        return implode('/', $parts);
    }

    public static function getFileName(string $path): string
    {
        $parts = self::splitPath($path);
        return (string) end($parts);
    }

    public static function startsWith(string $haystack, string $needle): bool
    {
        return str_starts_with($haystack, $needle);
    }

    public static function endsWith(string $haystack, string $needle): bool
    {
        $length = strlen($needle);
        if ($length === 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }
}
