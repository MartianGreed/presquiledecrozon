<?php

namespace App\Domain\Exception;

final class RentalSubscriptionNotFound extends \RuntimeException
{
    public function __construct(string $rentalId)
    {
        parent::__construct('RentalSubscription not found for rental with id: ' . $rentalId);
    }
}