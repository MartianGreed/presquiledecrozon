<?php

namespace App\Tests\Unit\App\Factory;

use App\Entity\Data\Country;
use App\Entity\Data\Department;
use App\Entity\Data\Region;

final class CountryFactory
{
    public static function france(): Country
    {
        $country = new Country();
        $country->setName('France')->setCode('FR');

        return $country;
    }

    public static function bretagne(): Region
    {
        $region = new Region();

        $region
            ->setName('Bretagne')
            ->setSlug('bretagne')
            ->setPrefix1('de')
            ->setPrefix2('en')
            ->setCountry(self::france())
        ;
        return $region;
    }

    public static function finistere(): Department
    {
        $department = new Department();

        $department
            ->setName('Finistère')
            ->setSlug('finistere')
            ->setRegion(self::bretagne())
        ;

        return $department;
    }
}
