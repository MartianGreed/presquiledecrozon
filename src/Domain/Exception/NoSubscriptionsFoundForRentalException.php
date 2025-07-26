<?php

namespace App\Domain\Exception;

final class NoSubscriptionsFoundForRentalException extends \DomainException
{
    public function __construct(string $rentalId)
    {
        parent::__construct('No active subscription found for rental id: '.$rentalId);
    }
}
