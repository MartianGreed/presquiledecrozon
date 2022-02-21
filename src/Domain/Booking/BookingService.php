<?php

namespace App\Domain\Booking;

use App\Entity\Booking\Booking;
use App\Entity\Rental\Rental;
use App\Repository\Booking\BookingRepository;

final class BookingService
{
    public function __construct(private readonly BookingRepository $bookingRepository)
    {
    }

    public function isRentalAvailableForBooking(Rental $rental, Booking $booking): bool {
        $bookings = $this->bookingRepository->findBookingsForPeriod(
            (string) $rental->getId(),
            (string) $booking->getId(),
            (string) $booking->getStartAt()?->format('Y-m-d H:i:s'),
            (string) $booking->getEndAt()?->format('Y-m-d H:i:s')
        );

        return 0 === \count($bookings);
    }
}