<?php

namespace App\DataFixtures;

use App\Entity\Data\Department;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class DepartmentFixtures extends Fixture implements DependentFixtureInterface
{
    public const DEPARTMENT_FINISTERE = 'department-finistere';

    public function load(ObjectManager $manager): void
    {
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
