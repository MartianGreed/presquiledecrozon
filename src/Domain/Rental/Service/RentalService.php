<?php

namespace App\Domain\Rental\Service;

use App\Domain\Exception\EntityNotFoundException;
use App\Entity\Rental\Address;
use App\Entity\Rental\Description;
use App\Entity\Rental\Rental;
use App\Entity\User;
use App\Message\FetchRentalGeolocation;
use App\Repository\Rental\RentalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class RentalService
{
    private EntityManagerInterface $manager;
    private RentalRepository $rentalRepository;
    private MessageBusInterface $bus;

    public function __construct(
        EntityManagerInterface $manager,
        RentalRepository $rentalRepository,
        MessageBusInterface $bus
    ) {
        $this->manager = $manager;
        $this->rentalRepository = $rentalRepository;
        $this->bus = $bus;
    }

    public function findOrCreateRental(User $user): Rental
    {
        try {
            return $this->rentalRepository->findLatestDraftRentalForUser($user->getId());
        } catch (EntityNotFoundException $e) {
            $rental = Rental::new($user);

            $this->manager->persist($rental);
            $this->manager->flush();

            return $rental;
        }
    }

    public function saveDescription(Rental $rental, Description $description): Rental
    {
        $rental->saveDescription($description);

        $this->manager->persist($rental->getDescription());
        $this->saveEntity($rental);

        return $rental;
    }

    public function saveAddress(Rental $rental, Address $address): Rental
    {
        $rental->saveAddress($address);

        $this->manager->persist($rental->getAddress());
        $this->saveEntity($rental);

        $address = $rental->getAddress();
        $this->bus->dispatch(
            new FetchRentalGeolocation(
                $rental->getId(),
                $address->getAddress(),
                $address->getTown()->getName(),
                $address->getTown()->getPostalCode()->getCode(),
                $address->getAddress2(),
            )
        );

        return $rental;
    }

    public function saveEntity(Rental $rental): void
    {
        $this->manager->flush();
    }
}