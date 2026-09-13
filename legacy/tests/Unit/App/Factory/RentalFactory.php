<?php

declare(strict_types=1);

namespace App\Tests\Unit\App\Factory;

use App\Domain\Price;
use App\Entity\Rental\Rental;
use App\Entity\User;

final class RentalFactory
{
    public static function create(
        User $owner,
        Price $nightlyPrice = new Price(60),
        Price $weeklyPrice = new Price(500),
    ): Rental {
        return Rental::new($owner)->setDailyRate($nightlyPrice)->setWeeklyRate($weeklyPrice);
    }
}
