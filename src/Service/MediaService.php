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
    private OptionsResolver $resolver;

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
     * @param string|null  $fieldName
     * @param string|null  $className
     * @param ResizeOption|array<string, mixed> $options
     *
     * @return string
     */
    public function assetHelper($obj, ?string $fieldName = null, ?string $className = null, array $options = []): string
    {
        if (0 === \count($options)) {
            return (string) $this->helper->asset($obj, $fieldName, $className);
        }

        $options = $this->resolveOptions($options);

        if (!$this->cacheManager->has($obj, $fieldName, $className, $options)) {
            $this->resizer->resize(
                $this->cacheManager->getSourceFilePath($obj, $fieldName, $className, $options),
                $this->cacheManager->getPath($obj, $fieldName, $className, $options),
                $options
            );
        }

        return $this->cdnHost . $this->cacheManager->getPath($obj, $fieldName, $className, $options);
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