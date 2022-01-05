<?php

namespace App\Tests\Unit\App\Factory\Rental;

use App\Entity\Data\Town;
use App\Entity\Rental\Address;
use App\Entity\Rental\Rental;

final class AddressFactory
{
    public static function createAddress(string $addressStr, Town $town, ?Rental $rental = null, ?string $address2 = null): Address
    {
        $address = new Address();

        $address
            ->setAddress($addressStr)
            ->setTown($town)
            ->setAddress2($address2)
        ;

        if (null !== $rental) {
            $address->setRental($rental);
        }

        return $address;
    }
}
