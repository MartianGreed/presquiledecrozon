<?php

namespace App\Message;

final class ResizeRentalPictures
{
    public function __construct(
        public readonly string $rentalId,
        public readonly string $galleryId,
    ) {
    }
}
