<?php

namespace App\MessageHandler\Traits;

use App\Domain\Exception\RentalNotFoundException;
use App\Entity\Rental\Rental;
use App\Repository\Rental\RentalRepository;
use Psr\Log\LoggerInterface;

trait RentalFetcherTrait
{
    final public function withRental(
        RentalRepository $repository,
        LoggerInterface $logger,
        string $rentalId
    ): Rental {
        $rental = $repository->find($rentalId);
        if (null === $rental) {
            $logger->error('Published rental with id : ' . $rentalId . ' not found.');
            throw new RentalNotFoundException($rentalId);
        }

        return $rental;
    }
}