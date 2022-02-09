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
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly BedRepository $bedRepository)
    {
    }

    public function createConfiguration(Rental $rental, ConfigurationDTO $configurationDTO): Configuration
    {
        $configuration = null === $rental->getConfiguration()
            ? $this->createConfigurationObjectFromDTO($rental, $configurationDTO)
            : $this->updateConfigurationObjectFromDTO($rental, $configurationDTO)
        ;

        $rental->setConfiguration($configuration);

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
        $configuration = $rental->getConfiguration();

        if (null === $configuration) {
            throw new \LogicException('Configuration cannot be null when updating');
        }

        return $this->hydrateConfigurationObjectFromDTO($configuration, $configurationDTO);
    }

    private function hydrateConfigurationObjectFromDTO(Configuration $configuration, ConfigurationDTO $configurationDTO): Configuration
    {
        $configuration->clearBedrooms();

        foreach ($configurationDTO->bedrooms as $bedroomItem) {
            $bedroom = new Bedroom();
            foreach ($bedroomItem as $bedId => $bedCount) {
                if (0 >= $bedCount) {
                    continue;
                }

                $bed = $this->bedRepository->find($bedId);
                if (null === $bed) {
                    throw new \LogicException('Bed with ID: ' . $bedId . ' does not exists');
                }

                $bedroom->addBed($bed);
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
