<?php

namespace App\Domain;

enum Notifications: string
{
    case RENTAL_HAS_BEEN_PUBLISHED = 'Une nouvelle annonce vient d\'être publiée';
}