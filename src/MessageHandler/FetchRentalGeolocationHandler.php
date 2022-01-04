<?php

namespace App\MessageHandler;

use App\Entity\Rental\Geolocation;
use App\Message\FetchRentalGeolocation;
use App\Repository\Rental\RentalRepository;
use App\Service\GeocodingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class FetchRentalGeolocationHandler implements MessageHandlerInterface
{
    private RentalRepository $rentalRepository;
    private GeocodingService $geocodingService;
    private EntityManagerInterface $manager;

    public function __construct(RentalRepository $rentalRepository, GeocodingService $geocodingService, EntityManagerInterface $manager)
    {
        $this->rentalRepository = $rentalRepository;
        $this->geocodingService = $geocodingService;
        $this->manager = $manager;
    }

    public function __invoke(FetchRentalGeolocation $message)
    {
        $rental = $this->rentalRepository->find($message->getRentalId());
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
