<?php

namespace App\Infrastructure\Symfony\DataFixtures;

use App\Entity\Data\PostalCode;
use App\Entity\Data\Town;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TownFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var PostalCode $postal29160 */
        $postal29160 = $this->getReference(PostalCodeFixtures::POSTAL_29160);
        /** @var PostalCode $postal29560 */
        $postal29560 = $this->getReference(PostalCodeFixtures::POSTAL_29560);
        /** @var PostalCode $postal29570 */
        $postal29570 = $this->getReference(PostalCodeFixtures::POSTAL_29570);

        $this->createTown($manager, 'Crozon', 'crozon', '29042', $postal29160);
        $this->createTown($manager, 'Camaret-sur-Mer', 'camaret-sur-mer', '29022', $postal29570);
        $this->createTown($manager, 'Roscanvel', 'roscanvel', '29238', $postal29570);
        $this->createTown($manager, 'Landévennec', 'landevennec', '29104', $postal29560);
        $this->createTown($manager, 'Argol', 'argol', '29001', $postal29560);
        $this->createTown($manager, 'Telgruc-sur-Mer', 'telgruc-sur-mer', '29280', $postal29560);
        $this->createTown($manager, 'Lanvéoc', 'lanveoc', '29120', $postal29160);

        $manager->flush();
    }

    private function createTown(
        ObjectManager $manager,
        string $name,
        string $slug,
        string $inseeCode,
        PostalCode $postalCode
    ): Town {
        $town = (new Town())->setName($name)->setSlug($slug)->setInseeCode($inseeCode)->setPostalCode($postalCode);

        $manager->persist($town);

        return $town;
    }

    public function getDependencies()
    {
        return [
            PostalCodeFixtures::class
        ];
    }

    public static function getGroups(): array
    {
        return ['data'];
    }
}
