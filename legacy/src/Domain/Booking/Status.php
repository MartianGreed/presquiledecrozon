<?php

namespace App\Domain\Booking;

enum Status: string
{
    case INITIALISED = 'initialised';
    case BOOKED = 'booked';
    case CONFIRMED = 'confirmed';
    case DONE = 'done';
    case CANCELED = 'cancelled';

    public function translate(): string
    {
        return match ($this) {
            self::INITIALISED => 'Initialisée',
            self::BOOKED => 'Réservation demandée',
            self::CONFIRMED => 'Réservation confirmée',
            self::DONE => 'Passée',
            self::CANCELED => 'Réservation annulée',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::INITIALISED => 'info',
            self::BOOKED => 'primary',
            self::CONFIRMED => 'success',
            self::DONE => 'secondary',
            self::CANCELED => 'danger',
        };
    }
}
