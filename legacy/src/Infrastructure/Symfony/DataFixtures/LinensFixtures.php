<?php

namespace App\Infrastructure\Symfony\DataFixtures;

use App\Entity\Data\Linens;
use App\Entity\Data\LinensCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LinensFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    /**
     * @var non-empty-array<string>
     */
    public static array $bathLinens = [
        'Serviette de toilette',
        'Drap de plage',
        'Drap de bain',
    ];

    /**
     * @var non-empty-array<string>
     */
    public static array $houseLinens = [
        'Serviette de table',
        'Torchons',
    ];

    /**
     * @var non-empty-array<string>
     */
    public static array $nightLinens = [
        'Drap housse',
        'Housse de couette',
        'Taie d\'oreiller',
        'Traversin',
    ];

    /**
     * @var non-empty-array<string>
     */
    public static array $literyLinens = [
        'Oreiller/traversin',
        'Couette',
    ];

    final public function load(ObjectManager $manager): void
    {
        $this->createLinensForCategory($manager, static::$bathLinens, LinensCategoryFixtures::LINENS_BAIN);
        $this->createLinensForCategory($manager, static::$houseLinens, LinensCategoryFixtures::LINENS_HOUSE);
        $this->createLinensForCategory($manager, static::$nightLinens, LinensCategoryFixtures::LINENS_NIGHT);
        $this->createLinensForCategory($manager, static::$literyLinens, LinensCategoryFixtures::LINENS_LITTERY);

        $manager->flush();
    }

    /**
     * @param non-empty-array<string> $names
     */
    private function createLinensForCategory(ObjectManager $manager, array $names, string $categoryKey): void
    {
        /** @var LinensCategory $category */
        $category = $this->getReference($categoryKey, LinensCategory::class);
        foreach ($names as $item) {
            $linen = (new Linens())->setLabel($item);
            $category->addLinen($linen);

            $manager->persist($linen);
        }
    }

    public function getDependencies(): array
    {
        return [LinensCategoryFixtures::class];
    }

    public static function getGroups(): array
    {
        return ['data'];
    }
}
