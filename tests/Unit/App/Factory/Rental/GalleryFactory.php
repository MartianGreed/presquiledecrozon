<?php

namespace App\Tests\Unit\App\Factory\Rental;

use App\Entity\Media;
use App\Entity\Rental\Gallery;

final class GalleryFactory
{
    public static function new(): Gallery
    {
        return new Gallery();
    }

    /**
     * @param array<Media> $pictures
     */
    public static function withMedia(Gallery $gallery, Media $coverPicture, array $pictures): Gallery
    {
        $gallery->setCover($coverPicture);
        foreach ($pictures as $picture) {
            $gallery->addPicture($picture);
        }

        return $gallery;
    }
}
