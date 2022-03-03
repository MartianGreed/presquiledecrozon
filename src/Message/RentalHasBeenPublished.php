<?php

namespace App\Message;

final class RentalHasBeenPublished
{
    public function __construct(
        public readonly string $rentalId,
        public readonly string $subscriptionId,
        public readonly string $occuredAt,
    ) {
    }
}
