<?php

namespace App\Service;

use App\Domain\Rental\PictureResizerOptions;
use App\Infrastructure\BunnyCDN\BunnyCDNAdapter;
use Intervention\Image\ImageManager;
use League\Flysystem\Config;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-import-type ResizeOption from PictureResizerOptions
 */
final class ImageResizerService implements ImageResizerServiceInterface
{
    private readonly ImageManager $imageManager;
    private readonly OptionsResolver $resolver;

    public function __construct(private readonly BunnyCDNAdapter $storage)
    {
        $this->imageManager = new ImageManager(['driver' => 'imagick']);
        $this->resolver = new OptionsResolver();
    }

    /** @param ResizeOption $options */
    public function resize(string $img, string $cachePath, array $options): void
    {
        $options = $this->resolveOptions($options);

        $fileExists = $this->storage->fileExists($img);

        $stream = $this->storage->readStream($img);

        $image = $this->imageManager->make($stream);
        $image->resize($options['w'], $options['h']);

        $this->storage->write($cachePath, $image->encode('jpeg')->stream(), (new Config())->withDefaults([]));
    }

    /**
     * @param ResizeOption $options
     *
     * @return ResizeOption
     */
    private function resolveOptions(array $options): array
    {
        $this->resolver->setRequired(['h', 'w', 'crop']);
        /** @var ResizeOption $options */
        $options = $this->resolver->resolve($options);
        return $options;
    }
}
