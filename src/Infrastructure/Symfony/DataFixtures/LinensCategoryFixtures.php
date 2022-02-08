<?php

namespace App\Infrastructure\Symfony\DataFixtures;

use App\Entity\Data\LinensCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LinensCategoryFixtures extends Fixture
{
    final public const LINENS_BAIN = 'linens-bain';
    final public const LINENS_HOUSE = 'linens-house';
    final public const LINENS_NIGHT = 'linens-night';
    final public const LINENS_LITTERY = 'linens-littery';

    public function load(ObjectManager $manager): void
    {
        $bathLinens = $this->createLinensCategory($manager, 'Linge de bain');
        $this->addReference(self::LINENS_BAIN, $bathLinens);

        $houseLinens = $this->createLinensCategory($manager, 'Linge de maison');
        $this->addReference(self::LINENS_HOUSE, $houseLinens);

        $nightLinens = $this->createLinensCategory($manager, 'Linge de lit');
        $this->addReference(self::LINENS_NIGHT, $nightLinens);

        $literyLinens = $this->createLinensCategory($manager, 'Literie');
        $this->addReference(self::LINENS_LITTERY, $literyLinens);

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
