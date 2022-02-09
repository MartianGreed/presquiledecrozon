<?php

namespace App\Infrastructure\Symfony\DataFixtures;

use App\Entity\Data\Country;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class CountryFixtures extends Fixture implements FixtureGroupInterface
{
    final public const COUNTRY_FRANCE = 'country-france';

    public function load(ObjectManager $manager): void
    {
        $country = new Country();
        $country->setName('France')->setCode('FR');

        $manager->persist($country);

        $manager->flush();

        $this->addReference(self::COUNTRY_FRANCE, $country);
    }

    public static function getGroups(): array
    {
        return ['data'];
    }
}
