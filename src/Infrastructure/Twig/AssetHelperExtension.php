<?php

namespace App\Infrastructure\Twig;

use App\Service\MediaService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

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
