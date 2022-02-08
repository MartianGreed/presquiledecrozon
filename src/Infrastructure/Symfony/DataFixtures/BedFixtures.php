<?php

namespace App\Infrastructure\Symfony\DataFixtures;

use App\Domain\Rental\BedSize;
use App\Entity\Data\Bed;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BedFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createBed('King size, 2 pers', new BedSize(200, 180)));
        $manager->persist($this->createBed('Queen size, 2 pers', new BedSize(200, 160)));
        $manager->persist($this->createBed('Lit double standard, 2 pers', new BedSize(200, 140), 'ou 140x190 cm'));
        $manager->persist($this->createBed('Canapé lit, 2 pers', null));
        $manager->persist($this->createBed('Lits superposés, 2 pers', new BedSize(190, 90)));
        $manager->persist($this->createBed('Lit, 1 pers', new BedSize(190, 90)));
        $manager->persist($this->createBed('Lit bébé', null));

        $manager->flush();
    }

    private function createBed(string $label, ?BedSize $size, ?string $help = null): Bed
    {
        $bed = new Bed();

        $bed
            ->setLabel($label)
            ->setHelp($help)
            ->setSize($size)
        ;

        return $bed;
    }
}
