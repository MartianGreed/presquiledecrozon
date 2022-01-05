<?php

namespace App\DataFixtures;

use App\Entity\Data\Country;
use App\Entity\Data\Region;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RegionFixtures extends Fixture implements DependentFixtureInterface
{
    final public const REGION_BRETAGNE = 'region-bretagne';

    public function load(ObjectManager $manager): void
    {
        $region = new Region();

        /** @var Country $country */
        $country = $this->getReference(CountryFixtures::COUNTRY_FRANCE);

        $region
            ->setName('Bretagne')
            ->setSlug('bretagne')
            ->setPrefix1('de')
            ->setPrefix2('en')
            ->setCountry($country)
        ;

        $this->addReference(self::REGION_BRETAGNE, $region);

        $manager->persist($region);
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CountryFixtures::class,
        ];
    }
}
