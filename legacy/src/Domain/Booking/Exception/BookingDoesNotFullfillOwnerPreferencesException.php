<?php

namespace App\Domain\Booking\Exception;

use App\Domain\Booking\DateIntervalTranslator;

final class BookingDoesNotFullfillOwnerPreferencesException extends \DomainException
{
    public function __construct(string $acceptedLastBookingInterval, string $maxTimeInterval)
    {
        parent::__construct(sprintf(
            'Les réservations pour ce logement sont possible %s à l\'avance et jusque %s avant la date de début de la location',
            DateIntervalTranslator::from($maxTimeInterval)->translate(),
            DateIntervalTranslator::from($acceptedLastBookingInterval)->translate(),
        ));
    }
}
