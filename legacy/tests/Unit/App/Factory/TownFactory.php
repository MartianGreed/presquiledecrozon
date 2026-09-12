<?php

namespace App\Tests\Unit\App\Factory;

use App\Entity\Data\Town;

final class TownFactory
{
    public static function crozon(): Town
    {
        $town = new Town();

        $town->setName('Crozon')
            ->setInseeCode('29042')
            ->setSlug('crozon')
            ->setPostalCode(PostalCodeFactory::create29560())
        ;

        return $town;
    }

    public static function argol(): Town
    {
        $town = new Town();

        $town->setName('Argol')
            ->setInseeCode('29001')
            ->setSlug('argol')
            ->setPostalCode(PostalCodeFactory::create29560())
        ;

        return $town;
    }
}
