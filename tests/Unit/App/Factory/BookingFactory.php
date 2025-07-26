<?php

declare(strict_types=1);

namespace App\Tests\Unit\App\Factory;

use App\Domain\Booking\BookingValidator;
use App\Entity\Booking\Booking;
use App\Entity\Rental\Rental;
use App\Entity\User;

final class BookingFactory
{
    public static function create(
        BookingValidator $validator,
        Rental $rental,
        User $booker,
        \DateTime $startAt,
        \DateTime $endAt,
        int $peopleCount,
    ): Booking {
        return Booking::init($validator, $rental, $booker, $startAt, $endAt, $peopleCount);
    }
}
