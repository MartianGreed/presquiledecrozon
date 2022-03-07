<?php

namespace App\Message;

final class RentalHasBeenBooked
{
    public function __construct(public readonly string $rentalId, public readonly string $bookingId, public readonly string $bookedAt)
    {
    }
}
