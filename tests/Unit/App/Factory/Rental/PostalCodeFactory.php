<?php

namespace App\Tests\Unit\App\Factory\Rental;

use App\Entity\Data\PostalCode;

final class PostalCodeFactory
{
    final public static function create29560(): PostalCode
    {
        $postalCode = new PostalCode();

        $postalCode
            ->setCode('29560')
            ->setDepartment(CountryFactory::finistere())
        ;

        return $postalCode;
    }
}