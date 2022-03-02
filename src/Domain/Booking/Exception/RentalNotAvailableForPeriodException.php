<?php

namespace App\Domain\Booking\Exception;


final class RentalNotAvailableForPeriodException extends \DomainException
{
    public function __construct(\DateTimeInterface $startAt, \DateTimeInterface $endAt)
    {
        parent::__construct(sprintf(
            'Le logement n\'est pas disponible pendant la période suivante : %s - %s',
            $startAt->format('d/m/Y'),
            $endAt->format('d/m/Y'),
        ));
    }
}