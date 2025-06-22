<?php

namespace App\Infrastructure\Symfony\DataFixtures;

use App\Entity\Data\Department;
use App\Entity\Data\PostalCode;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PostalCodeFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    final public const POSTAL_29160 = 'postal-29160';
    final public const POSTAL_29560 = 'postal-29560';
    final public const POSTAL_29570 = 'postal-29570';

    public function load(ObjectManager $manager): void
    {
        /** @var Department $department */
        $department = $this->getReference(DepartmentFixtures::DEPARTMENT_FINISTERE, Department::class);

        /** @var PostalCode $postal29160 */
        $postal29160 = $this->createPostalCode('29160', $department);
        /** @var PostalCode $postal29560 */
        $postal29560 = $this->createPostalCode('29560', $department);
        /** @var PostalCode $postal29570 */
        $postal29570 = $this->createPostalCode('29570', $department);

        $manager->persist($postal29160);
        $manager->persist($postal29560);
        $manager->persist($postal29570);

        $this->addReference(self::POSTAL_29160, $postal29160);
        $this->addReference(self::POSTAL_29560, $postal29560);
        $this->addReference(self::POSTAL_29570, $postal29570);

        $manager->flush();
    }

    private function createPostalCode(string $code, Department $department): PostalCode
    {
        return (new PostalCode())->setCode($code)->setDepartment($department);
    }

    public function getDependencies(): array
    {
        return [
            DepartmentFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['data'];
    }
}
