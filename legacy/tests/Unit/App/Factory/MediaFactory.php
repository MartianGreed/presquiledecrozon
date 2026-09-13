<?php

namespace App\Tests\Unit\App\Factory;

use App\Entity\Media;

final class MediaFactory
{
    public static function new(string $name, int $size, ?string $alt = null): Media
    {
        return (new Media())->setName($name)->setSize($size)->setAlt($alt);
    }
}
