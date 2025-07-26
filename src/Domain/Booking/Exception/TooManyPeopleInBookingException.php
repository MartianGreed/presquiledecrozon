<?php

namespace App\Domain\Booking\Exception;

final class TooManyPeopleInBookingException extends \DomainException
{
    public function __construct(int $peopleCount)
    {
        parent::__construct('Le logement ne peut pas accueillir ' . $peopleCount . ' personnes.');
    }
}
