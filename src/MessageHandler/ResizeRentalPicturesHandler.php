<?php

namespace App\MessageHandler;

use App\Domain\Exception\GalleryNotFound;
use App\Domain\Rental\PictureResizerOptions;
use App\Entity\Media;
use App\Manager\ImageCacheManagerInterface;
use App\Message\ResizeRentalPictures;
use App\Repository\Rental\GalleryRepository;
use App\Service\ImageResizerServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @phpstan-import-type ResizeOption from PictureResizerOptions
 */
#[AsMessageHandler]
final class ResizeRentalPicturesHandler
{
    public function __construct(
        private readonly GalleryRepository $galleryRepository,
        private readonly ImageResizerServiceInterface $resizer,
        private readonly ImageCacheManagerInterface $cacheManager,
    ) {
    }

    public function __invoke(ResizeRentalPictures $message): void
    {
        $gallery = $this->galleryRepository->find($message->galleryId);
        if (null === $gallery) {
            throw new GalleryNotFound($message->galleryId);
        }


        foreach (PictureResizerOptions::getOptions() as $option) {
            /** @var Media $cover */
            $cover = $gallery->getCover();
            $this->resizeImageWithOption($cover, $option);

            /** @var Media $picture */
            foreach ($gallery->getPictures() as $picture) {
                $this->resizeImageWithOption($picture, $option);
            }
        }
    }

    /**
     * @param ResizeOption $options
     */
    private function resizeImageWithOption(Media $media, array $options): void
    {
        $this->resizer->resize(
            $this->cacheManager->getSourceFilePath($media, 'file', null, $options),
            $this->cacheManager->getPath($media, 'file', null, $options),
            $options
        );
    }
}
