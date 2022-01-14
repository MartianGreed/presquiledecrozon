<?php

namespace App\Domain\Exception;

final class GalleryNotFound extends \DomainException
{
    public function __construct(string $galleryId)
    {
        parent::__construct(
            sprintf('Gallery not found for id : %s', $galleryId),
            0,
            null
        );
    }
}
