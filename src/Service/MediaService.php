<?php

namespace App\Service;

use App\Domain\Rental\PictureResizerOptions;
use App\Infrastructure\VichUploader\ImageCacheManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * @phpstan-import-type ResizeOption from PictureResizerOptions
 */
final class MediaService
{
    private readonly OptionsResolver $resolver;

    public function __construct(
        private readonly UploaderHelper $helper,
        private readonly ImageCacheManager $cacheManager,
        private readonly ImageResizerService $resizer,
        private readonly string $cdnHost,
    ) {
        $this->resolver = new OptionsResolver();
    }

    /**
     * @param array<string, mixed>|object $obj
     * @param ResizeOption|array<string, mixed> $options
     *
     */
    public function assetHelper($obj, ?string $fieldName = null, ?string $className = null, array $options = []): string
    {
        if (0 === \count($options)) {
            $assetUrl = (string) $this->helper->asset($obj, $fieldName, $className);
            
            // Extract the path from the URL (remove protocol and host)
            $parsedUrl = parse_url($assetUrl);
            $path = $parsedUrl['path'] ?? '';
            
            // If we have a valid path, prepend the CDN host
            if ($path !== '') {
                return rtrim($this->cdnHost, '/') . $path;
            }
            
            // Fallback to original URL if parsing fails
            return $assetUrl;
        }

        $options = $this->resolveOptions($options);

        if (!$this->cacheManager->has($obj, $fieldName, $className, $options)) {
            try {
                $this->resizer->resize(
                    $this->cacheManager->getSourceFilePath($obj, $fieldName, $className, $options),
                    $this->cacheManager->getPath($obj, $fieldName, $className, $options),
                    $options
                );
            } catch (\Throwable $e) {
                // If resizing fails, return the original asset URL with CDN host
                $assetUrl = (string) $this->helper->asset($obj, $fieldName, $className);
                $parsedUrl = parse_url($assetUrl);
                $path = $parsedUrl['path'] ?? '';
                
                if ($path !== '') {
                    return rtrim($this->cdnHost, '/') . $path;
                }
                
                return $assetUrl;
            }
        }

        return rtrim($this->cdnHost, '/') . '/' . ltrim($this->cacheManager->getPath($obj, $fieldName, $className, $options), '/');
    }

    /**
     * @param ResizeOption|array<string, mixed> $options
     * @return ResizeOption
     */
    private function resolveOptions(array $options): array
    {
        $this->resolver->setDefaults(['crop' => true]);
        $this->resolver->setRequired(['w', 'h']);
        $this->resolver->setAllowedTypes('h', 'int');
        $this->resolver->setAllowedTypes('w', 'int');

        /** @var ResizeOption $options */
        $options = $this->resolver->resolve($options);
        return $options;
    }
}
