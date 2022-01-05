<?php

namespace App\Message;

final class FetchRentalGeolocation
{
    public function __construct(public readonly string $rentalId)
    {
    }
}
