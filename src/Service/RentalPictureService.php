<?php

namespace App\Service;

use App\Domain\Rental\DTO\UploadedPicture;
use App\Domain\Rental\Service\RentalService;
use App\Entity\Rental\Gallery;
use App\Entity\Rental\Rental;
use Doctrine\ORM\EntityManagerInterface;

final class RentalPictureService
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly RentalService $rentalService,
    )
    {
    }

    public function uploadPicture(Rental $rental, UploadedPicture $picture): Rental
    {
        return match($picture->field) {
            'picture' => $this->handlePictureUpload($rental, $picture),
            'cover' => $this->handleCoverUpload($rental, $picture),
            default => throw new \RuntimeException('This case cannot happen'),
        };
    }

    private function handlePictureUpload(Rental $rental, UploadedPicture $picture): Rental
    {
        $persist = $this->mustPersist($rental);
        $gallery = $this->getRentalGallery($rental);

        $gallery->addPictureAtIndex($picture->media, (int) $picture->index);
        $rental->setGallery($gallery);

        if ($persist) {
            $this->manager->persist($gallery);
        }
        $this->manager->persist($picture->media);
        $this->rentalService->saveEntity($rental);

        return $rental;
    }

    private function handleCoverUpload(Rental $rental, UploadedPicture $picture): Rental
    {
        $persist = $this->mustPersist($rental);
        $gallery = $this->getRentalGallery($rental);

        $gallery->setCover($picture->media);
        $rental->setGallery($gallery);

        if ($persist) {
            $this->manager->persist($gallery);
        }
        $this->manager->persist($picture->media);

        $this->rentalService->saveEntity($rental);

        return $rental;
    }

    private function getRentalGallery(Rental $rental): Gallery
    {
        return $rental->getGallery() ?? new Gallery();
    }

    private function mustPersist(Rental $rental): bool
    {
        return null === $rental->getGallery();
    }
}