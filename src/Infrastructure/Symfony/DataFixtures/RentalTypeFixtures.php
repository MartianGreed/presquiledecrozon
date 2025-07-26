<?php

namespace App\Infrastructure\Symfony\DataFixtures;

use App\Entity\Data\RentalType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class RentalTypeFixtures extends Fixture implements FixtureGroupInterface
{
    final public const RENTAL_TYPE_FLAT = 'rental-type-flat';

    final public const RENTAL_TYPE_HOUSE = 'rental-type-house';

    final public const RENTAL_TYPE_HOST = 'rental-type-host';

    final public const RENTAL_TYPE_CHALET = 'rental-type-chalet';

    final public const RENTAL_TYPE_TRAILER = 'rental-type-trailer';

    public function load(ObjectManager $manager): void
    {
        $flat = $this->createRentalType($manager, 'Appartement', 'appartement');
        $this->addReference(self::RENTAL_TYPE_FLAT, $flat);
        $house = $this->createRentalType($manager, 'Maison', 'maison');
        $this->addReference(self::RENTAL_TYPE_HOUSE, $house);
        $host = $this->createRentalType($manager, 'Chambre d\'hôte', 'chambre-hote');
        $this->addReference(self::RENTAL_TYPE_HOST, $host);
        $chalet = $this->createRentalType($manager, 'Châlet', 'chalet');
        $this->addReference(self::RENTAL_TYPE_CHALET, $chalet);
        $trailer = $this->createRentalType($manager, 'Mobil-Home', 'mobil-home');
        $this->addReference(self::RENTAL_TYPE_TRAILER, $trailer);

        $manager->flush();
    }

    private function createRentalType(ObjectManager $manager, string $label, string $value): RentalType
    {
        $rentalType = new RentalType();
        $rentalType->setLabel($label)->setValue($value);

        $manager->persist($rentalType);

        return $rentalType;
    }

    public static function getGroups(): array
    {
        return ['data'];
    }
}
