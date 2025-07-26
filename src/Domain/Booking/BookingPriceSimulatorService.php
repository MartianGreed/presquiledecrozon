<?php

namespace App\Domain\Booking;

use App\Domain\Exception\InvalidDateRangeException;
use App\Domain\Price;
use App\Entity\Booking\Booking;

final class BookingPriceSimulatorService
{
    public function simulate(BookingRequest $request): Price
    {
        if ($request->startAt > $request->endAt) {
            throw new InvalidDateRangeException();
        }

        $rental = $request->rental;

        return $rental->getPricesForRange($request->startAt, $request->endAt)->getTotalPrice();
    }

    public function aggregatePrices(Booking $booking): Booking
    {
        $startAt = $booking->getStartAt();
        $endAt = $booking->getEndAt();

        if (! $startAt instanceof \DateTimeInterface) {
            throw new \DomainException('Start date cannot be null');
        }
        if (! $endAt instanceof \DateTimeInterface) {
            throw new \DomainException('End date cannot be null');
        }

        return $booking->setPrices($booking->getRental()->getPricesForRange($startAt, $endAt));
    }
}
