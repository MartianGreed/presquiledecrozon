<?php

namespace App\Domain;

enum Notifications: string
{
    case RENTAL_HAS_BEEN_PUBLISHED = 'Une nouvelle annonce vient d\'être publiée';
    case RENTAL_HAS_BEEN_BOOKED = 'Une nouvelle réservation vient d\'être faite sur le site';
    case BOOKING_HAS_BEEN_CONFIRMED = 'Une demande de réservation vient d\'être validée !';
    case BOOKING_HAS_BEEN_CANCELLED = 'Une demande de réservation vient d\'être annulée...';
}