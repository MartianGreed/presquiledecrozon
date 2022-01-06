<?php

namespace App\MessageHandler;

use App\Domain\Rental\Service\GeocodingServiceInterface;
use App\Domain\Rental\DTO\GeolocationDTO;
use App\Entity\Rental\Geolocation;
use App\Message\FetchRentalGeolocation;
use App\Repository\Rental\RentalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @phpstan-import-type GeolocationDTOArray from GeolocationDTO
 */
final class FetchRentalGeolocationHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly RentalRepository $rentalRepository,
        private readonly GeocodingServiceInterface $geocodingService,
        private readonly EntityManagerInterface $manager
    ) {
    }

    public function __invoke(FetchRentalGeolocation $message): void
    {
        $rental = $this->rentalRepository->find($message->rentalId);
        if (null === $rental) {
            throw new \LogicException('Rental is null');
        }

        $address = $rental->getAddress();
        if (null === $address) {
            throw new \LogicException('Address is null');
        }

        $geolocationDTO = $this->geocodingService->geocode($address);

        $geolocation = Geolocation::new($geolocationDTO->toArray());

        $rental->setGeolocation($geolocation);

        $this->manager->persist($geolocation);
        $this->manager->flush();
    }
}
