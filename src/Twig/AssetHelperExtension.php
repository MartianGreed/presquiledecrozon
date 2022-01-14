<?php

namespace App\Twig;

use App\Domain\Rental\PictureResizerOptions;
use App\Infrastructure\VichUploader\ImageCacheManager;
use App\Service\ImageResizerService;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * @phpstan-import-type ResizeOption from PictureResizerOptions
 */
final class AssetHelperExtension extends AbstractExtension
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

    public function getFunctions(): array
    {
        return [
            new TwigFunction('uploaded_asset', [$this, 'assetHelper']),
        ];
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
