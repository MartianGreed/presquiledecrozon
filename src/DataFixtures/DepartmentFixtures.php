<?php

namespace App\DataFixtures;

use App\Entity\Data\Department;
use App\Entity\Data\Region;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class DepartmentFixtures extends Fixture implements DependentFixtureInterface
{
    final public const DEPARTMENT_FINISTERE = 'department-finistere';

    public function load(ObjectManager $manager): void
    {
        /** @var Region $region */
        $region = $this->getReference(RegionFixtures::REGION_BRETAGNE);
        $department = new Department();

        $department
            ->setName('Finistère')
            ->setSlug('finistere')
            ->setRegion($region)
        ;

        $this->addReference(self::DEPARTMENT_FINISTERE, $department);

        $manager->persist($department);
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            RegionFixtures::class,
        ];
    }
}
