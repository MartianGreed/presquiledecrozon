<?php

namespace App\Domain\Booking;

enum Status: string
{
    case INITIALISED = 'initialised';
    case BOOKED = 'booked';
    case CONFIRMED = 'confirmed';
    case DONE = 'done';
}