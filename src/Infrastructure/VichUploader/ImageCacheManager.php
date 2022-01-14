<?php

namespace App\Infrastructure\VichUploader;

use App\Domain\Rental\PictureResizerOptions;
use App\Infrastructure\BunnyCDN\Storage;
use App\Manager\ImageCacheManagerInterface;

/**
 * @phpstan-import-type ResizeOption from PictureResizerOptions
 */
final class ImageCacheManager implements ImageCacheManagerInterface
{
    public function __construct(
        private readonly VichUploaderMappingExtractor $mappingExtractor,
        private readonly Storage $cdnStorage,
        private readonly ResizedImagePathBuilder $imagePathBuilder,
    ) {
    }

    /**
     * @param object|array<string, mixed> $obj
     * @param ResizeOption|array<string, mixed> $options
     */
    public function has($obj, ?string $fieldName, ?string $className = null, array $options = []): bool
    {
        $fileUri = $this->getSourceFilePath($obj, $fieldName, $className, $options);
        $cachedResizedFile = $this->imagePathBuilder->buildPath($fileUri, $options);

        return $this->cdnStorage->fileExists($cachedResizedFile);
    }

    /**
     * @param object|array<string, mixed> $obj
     * @param ResizeOption|array<string, mixed> $options
     */
    public function getPath($obj, ?string $fieldName, ?string $className = null, array $options = []): string
    {
        $fileUri = $this->getSourceFilePath($obj, $fieldName, $className, $options);

        return $this->imagePathBuilder->buildPath($fileUri, $options);
    }

    /**
     * @param object|array<string, mixed> $obj
     * @param ResizeOption|array<string, mixed> $options
     */
    public function getSourceFilePath($obj, ?string $fieldName, ?string $className = null, array $options = []): string
    {
        $component = $this->mappingExtractor->buildMappingComponent($obj, $fieldName, $className);


        return (string) $component->getStorage()->resolveUri($obj, $fieldName, $className);
    }
}
