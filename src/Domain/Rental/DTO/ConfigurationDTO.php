<?php

namespace App\Domain\Rental\DTO;

use App\Entity\Data\RentalType;
use App\Entity\Rental\Configuration;

final class ConfigurationDTO
{
    public ?RentalType $rentalType = null;
    public int $peopleCount = 0;
    public int $bedroomCount = 0;
    public array $bedrooms = [];

    public static function fromEntity(Configuration $configuration): self
    {
        $self = new self();

        $self->rentalType = $configuration->getType();
        $self->peopleCount = $configuration->getPeopleCount();
        $self->bedroomCount = $configuration->getBedrooms()->count();

        for ($i = 0; $i < $self->bedroomCount; $i++) {
            $bedroom  = $configuration->getBedrooms()->get($i);
            $self->bedrooms[] = [];
            foreach ($bedroom->getBeds() as $bed) {
                if (!array_key_exists($bed->getId(), $self->bedrooms[$i])) {
                    $self->bedrooms[$i][$bed->getId()] = 0;
                }

                ++$self->bedrooms[$i][$bed->getId()];
            }
        }

        return $self;
    }

}