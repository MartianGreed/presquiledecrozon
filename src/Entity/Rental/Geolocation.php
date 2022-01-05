<?php

namespace App\Entity\Rental;

use App\Entity\IdentityTrait;
use App\Repository\Rental\GeolocationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GeolocationRepository::class)]
class Geolocation
{
    use IdentityTrait;

    #[ORM\OneToOne(mappedBy: 'geolocation', targetEntity: Rental::class, cascade: ['persist', 'remove'])]
    private Rental $rental;

    /** @param array<string, array<string|int>|float> $coordinates  */
    private function __construct(#[ORM\Column(type: 'json')] private array $coordinates)
    {
    }

    /** @param array<string, array<string|int>|float> $location  */
    final public static function new(array $location): self
    {
        if (!array_key_exists('lat', $location) || !array_key_exists('lng', $location)) {
            throw new \LogicException('Cannot create Geolocation without any coordinates');
        }

        return new self($location);
    }

    final public function attachRental(Rental $rental): self
    {
        return $this->setRental($rental);
    }

    /** @psalm-return array<string, array<string|int>|float> */
    public function getCoordinates(): ?array
    {
        return $this->coordinates;
    }

    /** @param array<string, array<string|int>|float> $coordinates */
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
