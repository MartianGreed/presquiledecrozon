<?php

namespace App\Service;

use App\Infrastructure\VichUploader\ImageCacheManager;
use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * @phpstan-import-type ResizeOption from \App\Domain\Rental\PictureResizerOptions
 */
final class MediaService
{
    private readonly OptionsResolver $resolver;

    public function __construct(
        private readonly UploaderHelper $helper,
        private readonly ImageCacheManager $cacheManager,
        private readonly ImageResizerService $resizer,
        private readonly string $cdnHost,
        #[Autowire('%kernel.environment%')]
        private readonly string $environment,
        private readonly AssetMapperInterface $assetMapper,
    ) {
        $this->resolver = new OptionsResolver();
    }

    /**
     * @param array<string, mixed>|object       $obj
     * @param ResizeOption|array<string, mixed> $options
     */
    public function assetHelper($obj, ?string $fieldName = null, ?string $className = null, array $options = []): string
    {
        if (0 === \count($options)) {
            $assetUrl = (string) $this->helper->asset($obj, $fieldName, $className);

            // If VichUploader returns null or empty string, return empty
            if ($assetUrl === '' || $assetUrl === '0') {
                return '';
            }

            // In dev environment, always return the URL as-is without CDN
            if ('dev' === $this->environment) {
                $mappedAsset = $this->assetMapper->getAsset($assetUrl);

                return $mappedAsset instanceof \Symfony\Component\AssetMapper\MappedAsset ? $mappedAsset->publicPath : '';
            }

            // In production, prepend CDN host
            // If it's already an absolute URL with a host, extract just the path
            if (str_starts_with($assetUrl, 'http://') || str_starts_with($assetUrl, 'https://')) {
                $parsedUrl = parse_url($assetUrl);
                $path = $parsedUrl['path'] ?? '';

                // If we have a valid path, prepend the CDN host
                if ('' !== $path) {
                    return rtrim($this->cdnHost, '/') . $path;
                }
            }

            // If it's already a relative path starting with /, prepend CDN host
            if (str_starts_with($assetUrl, '/')) {
                return rtrim($this->cdnHost, '/') . $assetUrl;
            }

            // For any other format, prepend CDN host with a slash
            return rtrim($this->cdnHost, '/') . '/' . ltrim($assetUrl, '/');
        }

        $options = $this->resolveOptions($options);

        if (! $this->cacheManager->has($obj, $fieldName, $className, $options)) {
            try {
                $this->resizer->resize(
                    $this->cacheManager->getSourceFilePath($obj, $fieldName, $className, $options),
                    $this->cacheManager->getPath($obj, $fieldName, $className, $options),
                    $options
                );
            } catch (\Throwable $e) {
                // If resizing fails, return the original asset URL
                $assetUrl = (string) $this->helper->asset($obj, $fieldName, $className);

                if ($assetUrl === '' || $assetUrl === '0') {
                    return '';
                }

                // In dev environment, return local path without CDN host
                if ('dev' === $this->environment) {
                    $mappedAsset = $this->assetMapper->getAsset($assetUrl);

                    return $mappedAsset instanceof \Symfony\Component\AssetMapper\MappedAsset ? $mappedAsset->publicPath : '';
                }

                // In production, handle the URL the same way as non-resized images
                if (str_starts_with($assetUrl, 'http://') || str_starts_with($assetUrl, 'https://')) {
                    $parsedUrl = parse_url($assetUrl);
                    $path = $parsedUrl['path'] ?? '';
                    if ('' !== $path) {
                        return rtrim($this->cdnHost, '/') . $path;
                    }
                }

                if (str_starts_with($assetUrl, '/')) {
                    return rtrim($this->cdnHost, '/') . $assetUrl;
                }

                return rtrim($this->cdnHost, '/') . '/' . ltrim($assetUrl, '/');
            }
        }

        $cachePath = $this->cacheManager->getPath($obj, $fieldName, $className, $options);

        // In dev environment, return local path without CDN host
        if ('dev' === $this->environment) {
            return $cachePath;
        }

        // In production, prepend CDN host
        return rtrim($this->cdnHost, '/') . '/' . ltrim($cachePath, '/');
    }

    /**
     * @param ResizeOption|array<string, mixed> $options
     *
     * @return ResizeOption
     */
    private function resolveOptions(array $options): array
    {
        $this->resolver->setDefaults([
            'crop' => true,
        ]);
        $this->resolver->setRequired(['w', 'h']);
        $this->resolver->setAllowedTypes('h', 'int');
        $this->resolver->setAllowedTypes('w', 'int');

        /** @var ResizeOption $options */
        $options = $this->resolver->resolve($options);

        return $options;
    }
}
