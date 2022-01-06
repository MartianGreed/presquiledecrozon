<?php

namespace App\Entity\Rental;

use App\Entity\IdentityTrait;
use App\Domain\Rental\DTO\GeolocationDTO;
use App\Repository\Rental\GeolocationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @phpstan-import-type GeolocationDTOArray from GeolocationDTO
 */
#[ORM\Entity(repositoryClass: GeolocationRepository::class)]
class Geolocation
{
    use IdentityTrait;

    #[ORM\OneToOne(mappedBy: 'geolocation', targetEntity: Rental::class, cascade: ['persist', 'remove'])]
    private Rental $rental;

    /** @param GeolocationDTOArray $coordinates  */
    private function __construct(#[ORM\Column(type: 'json')] private array $coordinates)
    {
    }

    /** @param GeolocationDTOArray $location  */
    final public static function new(array $location): self
    {
        return new self($location);
    }

    final public function attachRental(Rental $rental): self
    {
        return $this->setRental($rental);
    }

    /** @psalm-return GeolocationDTOArray */
    public function getCoordinates(): array
    {
        return $this->coordinates;
    }

    /** @param GeolocationDTOArray $coordinates */
    public function setCoordinates(array $coordinates): self
    {
        $this->coordinates = $coordinates;

        return $this;
    }

    public function getRental(): ?Rental
    {
        return $this->rental;
    }

    public function setRental(Rental $rental): self
    {
        if ($rental->getGeolocation() !== $this) {
            $rental->setGeolocation($this);
        }

        $this->rental = $rental;

        return $this;
    }
}
