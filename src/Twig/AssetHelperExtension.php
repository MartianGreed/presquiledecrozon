<?php

namespace App\Twig;

use App\Domain\Rental\PictureResizerOptions;
use App\Infrastructure\VichUploader\ImageCacheManager;
use App\Service\ImageResizerService;
use App\Service\MediaService;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

final class AssetHelperExtension extends AbstractExtension
{
    public function __construct(private readonly MediaService $mediaService)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('uploaded_asset', [$this->mediaService, 'assetHelper']),
        ];
    }
}
