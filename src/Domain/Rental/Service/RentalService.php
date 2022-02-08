<?php

namespace App\Domain\Rental\Service;

use App\Domain\Exception\EntityNotFoundException;
use App\Domain\Exception\RentalNotFoundException;
use App\Domain\Rental\DTO\GeolocationDTO;
use App\Domain\Rental\DTO\LocationSuggestion;
use App\Domain\Rental\Status;
use App\Entity\Rental\Address;
use App\Entity\Rental\Condition;
use App\Entity\Rental\Description;
use App\Entity\Rental\Gallery;
use App\Entity\Rental\Geolocation;
use App\Entity\Rental\Preferences;
use App\Entity\Rental\Rental;
use App\Entity\Rental\Tax;
use App\Entity\Rental\Unavailability;
use App\Entity\User;
use App\Message\FetchRentalGeolocation;
use App\Repository\Rental\RentalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class RentalService
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly RentalRepository $rentalRepository,
        private readonly MessageBusInterface $bus,
        private readonly RentalImproveLocalisationService $improveLocalisationService,
    ) {
    }

    public function findOrCreateRental(User $user): Rental
    {
        try {
            return $this->rentalRepository->findLatestDraftRentalForUser((string) $user->getId());
        } catch (EntityNotFoundException) {
            $rental = Rental::new($user);

            $this->manager->persist($rental);
            $this->manager->flush();

            return $rental;
        }
    }

    public function findLatestDraftRental(User $user): Rental
    {
        return $this->rentalRepository->findLatestDraftRentalForUser((string) $user->getId());
    }

    public function saveDescription(Rental $rental, Description $description): Rental
    {
        $rental->saveDescription($description);

        /** @var Description $rentalDescription */
        $rentalDescription = $rental->getDescription();

        $this->manager->persist($rentalDescription);
        $this->saveEntity($rental);

        return $rental;
    }

    public function saveAddress(Rental $rental, Address $address): Rental
    {
        $rental->saveAddress($address);

        /** @var Address $rentalAddress */
        $rentalAddress = $rental->getAddress();

        $this->manager->persist($rentalAddress);
        $this->saveEntity($rental);

        $this->bus->dispatch(
            new FetchRentalGeolocation((string) $rental->getId())
        );

        return $rental;
    }

    /** @param array<Unavailability> $unavailabilities */
    public function saveUnavailabilities(Rental $rental, array $unavailabilities): Rental
    {
        $rental->saveUnavailabilities($unavailabilities);
        $this->saveEntity($rental);

        return $rental;
    }

    public function saveEntity(Rental $rental): void
    {
        $this->manager->flush();
    }

    public function improveLocalisation(Rental $rental, GeolocationDTO $geolocationDTO, ?string $suggestion = null, ?string $suggestionMetadata = null): Rental
    {
        $suggestions = new LocationSuggestion(
            (string) $suggestion,
            (string) $suggestionMetadata,
        );

        $rental = $this->improveLocalisationService->improveLocalisation($rental, $geolocationDTO, $suggestions);

        /** @var Geolocation $geolocation */
        $geolocation = $rental->getGeolocation();

        $this->manager->persist($geolocation);
        $this->saveEntity($rental);


        return $rental;
    }

    public function savePictures(Rental $rental, Gallery $gallery): Rental
    {
        $rental = $rental->createGallery($gallery);

        /** @var Gallery $gallery */
        $gallery = $rental->getGallery();

        $this->manager->persist($gallery);
        $this->manager->flush();

        return $rental;
    }

    public function savePreferences(Rental $rental, Preferences $preferences): Rental
    {
        $rental->setPreferences($preferences);

        $this->manager->persist($preferences);
        $this->manager->flush();

        return $rental;
    }

    public function saveTax(Rental $rental, Tax $tax): Rental
    {
        $rental = $rental->saveTax($tax);

        $this->manager->persist($tax);
        $this->saveEntity($rental);

        return $rental;
    }

    public function saveConditions(Rental $rental, Condition $condition): Rental
    {
        $rental = $rental->setCondition($condition);

        $rental->setStatus(Status::VALID);

        $this->manager->persist($condition);
        $this->saveEntity($rental);

        return $rental;
    }

    public function findRental(string $rentalId): Rental
    {
        try {
            $rental = $this->rentalRepository->find($rentalId);
            if (null === $rental) {
                throw new RentalNotFoundException($rentalId);
            }
            return $rental;
        } catch (\Exception $e) {
            throw new RentalNotFoundException($rentalId);
        }
    }
}
