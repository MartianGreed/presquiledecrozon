<?php

namespace App\Domain\Booking;

use App\Entity\Rental\Rental;

interface BookingValidator
{
    public function validateBooking(Rental $rental, \DateTimeInterface $startAt, \DateTimeInterface $endAt, int $peopleCount): bool;
}
