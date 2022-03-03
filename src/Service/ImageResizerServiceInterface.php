<?php

namespace App\Service;

use App\Domain\Rental\PictureResizerOptions;

/**
 * @phpstan-import-type ResizeOption from PictureResizerOptions
 */
interface ImageResizerServiceInterface
{
    /** @param ResizeOption $options */
    public function resize(string $img, string $cachePath, array $options): void;
}
