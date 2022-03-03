<?php

namespace App\Domain\Booking;

enum DateIntervalTranslator: string
{
    case P1M = 'P1M';
    case P2M = 'P2M';
    case P3M = 'P3M';
    case P4M = 'P4M';
    case P6M = 'P6M';
    case P9M = 'P9M';
    case P1Y = 'P1Y';

    case P1D = 'P1D';
    case P3D = 'P3D';
    case P5D = 'P5D';
    case P7D = 'P7D';

    public function translate(): string
    {
        return match ($this) {
            self::P1M => '1 mois',
            self::P2M => '2 mois',
            self::P3M => '3 mois',
            self::P4M => '4 mois',
            self::P6M => '6 mois',
            self::P9M => '9 mois',
            self::P1Y => '1 an',
            self::P1D => '1 jour',
            self::P3D => '3 jours',
            self::P5D => '5 jours',
            self::P7D => '1 semaine',
        };
    }
}
