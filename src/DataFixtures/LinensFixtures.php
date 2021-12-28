<?php

namespace App\DataFixtures;

use App\Entity\Data\Linens;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LinensFixtures extends Fixture implements DependentFixtureInterface
{
    public static $bathLinens = [
        'Serviette de toilette',
        'Drap de plage',
        'Drap de bain',
    ];
    public static $houseLinens = [
        'Serviette de table',
        'Torchons',
    ];
    public static $nightLinens = [
        'Drap housse',
        'Housse de couette',
        'Taie d\'oreiller',
        'Traversin',
    ];

    public function load(ObjectManager $manager): void
    {
        $this->createLinensForCategory($manager, static::$bathLinens, LinensCategoryFixtures::LINENS_BAIN);
        $this->createLinensForCategory($manager, static::$houseLinens, LinensCategoryFixtures::LINENS_HOUSE);
        $this->createLinensForCategory($manager, static::$nightLinens, LinensCategoryFixtures::LINENS_NIGHT);

        $manager->flush();
    }

    private function createLinensForCategory(ObjectManager $manager, array $names, string $categoryKey): void
    {
        $category = $this->getReference($categoryKey);
        foreach ($names as $item) {
            $linen = (new Linens())->setLabel($item);
            $category->addLinen($linen);

            $manager->persist($linen);
        }
    }

    public function getDependencies()
    {
        return [LinensCategoryFixtures::class];
    }
}
