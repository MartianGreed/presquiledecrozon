<?php

namespace App\Infrastructure\Symfony\DataFixtures;

use App\Entity\Data\Country;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CountryFixtures extends Fixture
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
}
