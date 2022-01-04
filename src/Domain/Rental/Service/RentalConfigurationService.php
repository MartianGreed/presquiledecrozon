<?php

namespace App\Domain\Rental\Service;

use App\Domain\Rental\DTO\ConfigurationDTO;
use App\Entity\Rental\Bedroom;
use App\Entity\Rental\Configuration;
use App\Entity\Rental\Rental;
use App\Repository\Data\BedRepository;
use Doctrine\ORM\EntityManagerInterface;

final class RentalConfigurationService
{
    private BedRepository $bedRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, BedRepository $bedRepository)
    {
        $this->entityManager = $entityManager;
        $this->bedRepository = $bedRepository;
    }

    public function createConfiguration(Rental $rental, ConfigurationDTO $configurationDTO): Configuration
    {

        $configuration = null === $rental->getConfiguration()
            ? $this->createConfigurationObjectFromDTO($rental, $configurationDTO)
            : $this->updateConfigurationObjectFromDTO($rental, $configurationDTO)
        ;

        $this->entityManager->persist($configuration);
        $this->entityManager->flush();

        return $configuration;
    }


    private function createConfigurationObjectFromDTO(Rental $rental, ConfigurationDTO $configurationDTO): Configuration
    {
        $configuration = (new Configuration())->setRental($rental);

        return $this->hydrateConfigurationObjectFromDTO($configuration, $configurationDTO);
    }

    private function updateConfigurationObjectFromDTO(Rental $rental, ConfigurationDTO $configurationDTO): Configuration
    {
        return $this->hydrateConfigurationObjectFromDTO($rental->getConfiguration(), $configurationDTO);
    }

    private function hydrateConfigurationObjectFromDTO(Configuration $configuration, ConfigurationDTO $configurationDTO): Configuration
    {
        foreach ($configurationDTO->bedrooms as $bedroomItem) {
            $bedroom = new Bedroom();
            foreach ($bedroomItem as $bedId => $bedCount) {
                if (0 >= $bedCount) {
                    continue;
                }

                $bedroom->addBed($this->bedRepository->find($bedId));
            }

            $configuration->addBedroom($bedroom);
        }

        $configuration
            ->setPeopleCount($configurationDTO->peopleCount)
            ->setType($configurationDTO->rentalType)
        ;

        return $configuration;
    }
}