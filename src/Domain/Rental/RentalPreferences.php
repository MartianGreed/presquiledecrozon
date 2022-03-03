<?php

namespace App\Domain\Rental;

final class RentalPreferences
{
    /** @return array<string, string> */
    public static function acceptedLastBookingChoices(): array
    {
        return [
             'La veille' => 'P1D',
             '3 jours à l\'avance' => 'P3D',
             '5 jours à l\'avance' => 'P5D',
             'Une semaine à l\'avance' => 'P7D',
        ];
    }

    /** @return array<string, string> */
    public static function maxTimeBeforeBookingChoices(): array
    {
        return [
            '1 mois à l\'avance' => 'P1M',
            '2 mois à l\'avance' => 'P2M',
            '3 mois à l\'avance' => 'P3M',
            '4 mois à l\'avance' => 'P4M',
            '6 mois à l\'avance' => 'P6M',
            '9 mois à l\'avance' => 'P9M',
            '1 an à l\'avance' => 'P1Y',
        ];
    }

    /** @return array<string, string> */
    public static function beginBookingAt(): array
    {
        return [
            '11h' => '11:00',
            '12h' => '12:00',
            '13h' => '13:00',
            '14h' => '14:00',
            '15h' => '15:00',
            '16h' => '16:00',
            '17h' => '17:00',
            '18h' => '18:00',
            '19h' => '19:00',
            '20h' => '20:00',
            '21h' => '21:00',
        ];
    }

    /** @return array<string, string> */
    public static function endBookingAt(): array
    {
        return [
            '7h' => '07:00',
            '8h' => '08:00',
            '9h' => '09:00',
            '10h' => '10:00',
            '11h' => '11:00',
            '12h' => '12:00',
            '13h' => '13:00',
            '14h' => '14:00',
            '15h' => '15:00',
            '16h' => '16:00',
            '17h' => '17:00',
        ];
    }
}
