<?php

namespace App\Infrastructure\Symfony\DataFixtures;

use App\Entity\Data\Furniture;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class FurnitureFixtures extends Fixture implements FixtureGroupInterface
{
    /** @var non-empty-array<string>  */
    private array $furnitures = [
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
        foreach ($this->furnitures as $furniture) {
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

    public static function getGroups(): array
    {
        return ['data'];
    }
}
