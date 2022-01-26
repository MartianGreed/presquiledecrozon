<?php

namespace App\Infrastructure\VichUploader;

use App\Domain\Rental\PictureResizerOptions;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-import-type ResizeOption from PictureResizerOptions
 */
final class ResizedImagePathBuilder
{
    private readonly OptionsResolver $resolver;

    public function __construct(private readonly string $cachePrefix = '.cache')
    {
        $this->resolver = new OptionsResolver();
    }

    /** @param ResizeOption $options */
    public function buildPath(string $uri, array $options): string
    {
        return sprintf('/%s%s/%s', $this->cachePrefix, $uri, $this->buildFileName($options));
    }

    /** @param ResizeOption $options */
    private function buildFileName(array $options): string
    {
        /** @var ResizeOption $options */
        $options = $this->resolveOptions($options);

        return sprintf(
            '%d_%d.png',
            (int) $options['h']/** @phpstan-ignore-line */,
            (int) $options['w']/** @phpstan-ignore-line */,
        );
    }

    /**
     * @param ResizeOption $options
     * @return ResizeOption
     */
    private function resolveOptions(array $options): array
    {
        $this->resolver->setRequired(['h', 'w', 'crop']);
        $this->resolver->setDefined(['function']);

        /** @var ResizeOption $options */
        $options = $this->resolver->resolve($options);
        return $options;
    }
}
