<?php

namespace App\Domain\Exception;

final class RentalNotFoundException extends \DomainException
{
    public function __construct(string $rentalId)
    {
        parent::__construct('Rental not found for id: ' . $rentalId);
    }
}
