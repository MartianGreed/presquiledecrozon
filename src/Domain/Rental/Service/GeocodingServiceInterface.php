<?php

namespace App\Domain\Rental\Service;

use App\Domain\Rental\DTO\GeolocationDTO;
use App\Entity\Rental\Address;

interface GeocodingServiceInterface
{
    public function geocode(Address $address): GeolocationDTO;
}
