<?php

namespace App\Message;

final class BookingHasBeenCancelled
{
    public function __construct(public readonly string $bookingId, public readonly string $cancelledAt)
    {
    }
}
