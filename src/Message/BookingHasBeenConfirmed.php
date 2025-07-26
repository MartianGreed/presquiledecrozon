<?php

namespace App\Message;

final class BookingHasBeenConfirmed
{
    public function __construct(
        public readonly string $bookingId,
        public readonly string $confirmedAt
    )
    {
    }
}
