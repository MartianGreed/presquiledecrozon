<?php

namespace App\Manager;

use App\Domain\Rental\PictureResizerOptions;

/**
 * @phpstan-import-type ResizeOption from PictureResizerOptions
 */
interface ImageCacheManagerInterface
{
    /**
     * @param object|array<string, mixed>       $obj
     * @param ResizeOption|array<string, mixed> $options
     */
    public function has($obj, ?string $fieldName, ?string $className = null, array $options = []): bool;

    /**
     * @param object|array<string, mixed>       $obj
     * @param ResizeOption|array<string, mixed> $options
     */
    public function getPath($obj, ?string $fieldName, ?string $className = null, array $options = []): string;

    /**
     * @param object|array<string, mixed>       $obj
     * @param ResizeOption|array<string, mixed> $options
     */
    public function getSourceFilePath($obj, ?string $fieldName, ?string $className = null, array $options = []): string;
}
