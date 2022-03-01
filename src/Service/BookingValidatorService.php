<?php

namespace App\Service;

use App\Domain\Booking\BookingValidator;
use App\Domain\Booking\Exception\BookingDoesNotFullfillOwnerPreferencesException;
use App\Domain\Booking\Exception\RentalNotAvailableForPeriodException;
use App\Domain\Booking\Exception\TooManyPeopleInBookingException;
use App\Entity\Rental\Rental;
use App\Repository\Booking\BookingRepository;
use App\Repository\Rental\RentalRepository;

final class BookingValidatorService implements BookingValidator
{
    public function __construct(
        private readonly BookingRepository $bookingRepository,
        private readonly RentalRepository $rentalRepository,
    )
    {
    }

    public function validateBooking(Rental $rental, \DateTimeInterface $startAt, \DateTimeInterface $endAt, int $peopleCount): bool
    {
        if (!$this->bookingRepository->isBookingAvailableForPeriod((string) $rental->getId(), $startAt, $endAt)) {
            throw new RentalNotAvailableForPeriodException();
        }

        if ($rental->getConfiguration()?->getPeopleCount() < $peopleCount) {
            throw new TooManyPeopleInBookingException();
        }

        if ($this->rentalRepository->hasUnavailabilitiesForPeriod((string) $rental->getId(), $startAt, $endAt)) {
            throw new RentalNotAvailableForPeriodException();
        }

        // Check reservation preferences for rental
        $acceptedLastBookingInterval = $rental->getPreferences()?->getAcceptedLastBooking();
        $maxTimeInterval = $rental->getPreferences()?->getMaxTimeBeforeBooking();
        $now = new \DateTimeImmutable('now');
        if (
            $startAt <= $now->sub(new \DateInterval((string) $acceptedLastBookingInterval))
            || $startAt <= $now->sub(new \DateInterval((string) $maxTimeInterval))
        ) {
            throw new BookingDoesNotFullfillOwnerPreferencesException();
        }

        // If everything is ok, return true;
        return true;
    }
}