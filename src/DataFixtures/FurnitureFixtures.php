<?php

namespace App\DataFixtures;

use App\Entity\Data\Furniture;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FurnitureFixtures extends Fixture
{
    private static $furnitures = [
        'Piscine',
        'TV',
        'Garages / parking',
        'Jardin',
        'Accès internet',
        'Machine à laver',
        'Lit bébé',
        'Terrasse / balcon',
        'Lave-vaisselle',
        'Sauna / jacuzzi',
        'Barbecue',
        'Micro-ondes',
        'Sèche-linge',
        'Téléphone',
        'Planche et fer à repasser',
        'Cheminée',
        'Air-conditionné',
        'Sèche-cheveux',
        'Aspirateur',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (static::$furnitures as $furniture) {
            $this->createFurniture($manager, $furniture);
        }

        $manager->flush();
    }

    private function createFurniture(ObjectManager $manager, string $name): void
    {
        $furniture = new Furniture();
        $furniture->setName($name);

        $manager->persist($furniture);

    }
}
