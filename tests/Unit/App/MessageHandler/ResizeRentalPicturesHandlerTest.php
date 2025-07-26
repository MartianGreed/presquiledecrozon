<?php

namespace App\Tests\Unit\App\MessageHandler;

use App\Domain\Exception\GalleryNotFound;
use App\Domain\Rental\PictureResizerOptions;
use App\Entity\Rental\Gallery;
use App\Manager\ImageCacheManagerInterface;
use App\Message\ResizeRentalPictures;
use App\MessageHandler\ResizeRentalPicturesHandler;
use App\Repository\Rental\GalleryRepository;
use App\Service\ImageResizerServiceInterface;
use App\Tests\Unit\App\Factory\MediaFactory;
use App\Tests\Unit\App\Factory\Rental\GalleryFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ResizeRentalPicturesHandlerTest extends TestCase
{
    private MockObject $galleryRepository;

    private MockObject $imageResizerService;

    private MockObject $imageCacheManager;

    private ResizeRentalPicturesHandler $handler;

    public function setUp(): void
    {
        $this->galleryRepository = $this->createMock(GalleryRepository::class);
        $this->imageResizerService = $this->createMock(ImageResizerServiceInterface::class);
        $this->imageCacheManager = $this->createMock(ImageCacheManagerInterface::class);

        $this->handler = new ResizeRentalPicturesHandler($this->galleryRepository, $this->imageResizerService, $this->imageCacheManager);
    }

    public function testItThrowsExceptionWhenGalleryOrRentalAreNotFound(): void
    {
        $this->galleryRepository->expects($this->once())->method('find')->with('aFakeGalleryId')->willReturn(null);
        $this->expectException(GalleryNotFound::class);

        call_user_func($this->handler, new ResizeRentalPictures('aFakeRentalId', 'aFakeGalleryId'));
    }

    public function testItResizeAllImages(): void
    {
        $gallery = $this->createGallery();
        $this->galleryRepository->expects($this->once())->method('find')->with('aValidId')->willReturn($gallery);

        $this->imageResizerService->expects($this->exactly($gallery->getPicturesCount() * count(PictureResizerOptions::getOptions())))->method('resize');

        call_user_func($this->handler, new ResizeRentalPictures('aFakeRentalId', 'aValidId'));
    }

    private function createGallery(): Gallery
    {
        $gallery = GalleryFactory::new();

        return GalleryFactory::withMedia(
            $gallery,
            MediaFactory::new('fakecover.png', 1560),
            [
                MediaFactory::new('picture1.png', 1561),
                MediaFactory::new('picture2.png', 1562),
                MediaFactory::new('picture3.png', 1563),
                MediaFactory::new('picture4.png', 1564),
                MediaFactory::new('picture5.png', 1565),
            ]
        );
    }
}
