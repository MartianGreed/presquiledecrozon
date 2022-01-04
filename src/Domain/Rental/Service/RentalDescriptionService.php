<?php

namespace App\Domain\Rental\Service;

use App\Entity\Rental\Description;
use App\Entity\Rental\Rental;

final class RentalDescriptionService
{
    private RentalService $rentalService;

    public function __construct(RentalService $rentalService)
    {
        $this->rentalService = $rentalService;
    }

    public function saveDescription(Rental $rental, Description $description): Rental
    {
        $rental->saveDescription($description);

        $this->rentalService->persist($rental->getDescription());
        $this->rentalService->saveEntity($rental);

        return $rental;
    }
}