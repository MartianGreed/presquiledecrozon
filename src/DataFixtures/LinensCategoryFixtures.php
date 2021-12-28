<?php

namespace App\DataFixtures;

use App\Entity\Data\LinensCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LinensCategoryFixtures extends Fixture
{
    public const LINENS_BAIN = 'linens-bain';
    public const LINENS_HOUSE = 'linens-house';
    public const LINENS_NIGHT = 'linens-night';

    public function load(ObjectManager $manager): void
    {
        $bathLinens = $this->createLinensCategory($manager, 'Linge de bain');
        $this->addReference(self::LINENS_BAIN, $bathLinens);

        $houseLinens = $this->createLinensCategory($manager, 'Linge de maison');
        $this->addReference(self::LINENS_HOUSE, $houseLinens);

        $nightLinens = $this->createLinensCategory($manager, 'Linge de nuit');
        $this->addReference(self::LINENS_NIGHT, $nightLinens);

        $manager->flush();
    }

    private function createLinensCategory(ObjectManager $manager, string $name): LinensCategory
    {
        $linensCategory = new LinensCategory();
        $linensCategory->setName($name);

        $manager->persist($linensCategory);

        return $linensCategory;
    }
}
