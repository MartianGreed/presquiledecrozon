<?php

namespace App\Entity\Rental;

use App\Entity\IdentityTrait;
use App\Repository\Rental\GeolocationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GeolocationRepository::class)]
class Geolocation
{
    use IdentityTrait;

    #[ORM\Column(type: 'json')]
    private array $coordinates;

    #[ORM\OneToOne(mappedBy: 'geolocation', targetEntity: Rental::class, cascade: ['persist', 'remove'])]
    private Rental $rental;

    private function __construct(array $location)
    {
        $this->coordinates = $location;
    }

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

    public function getCoordinates(): ?array
    {
        return $this->coordinates;
    }

    public function setCoordinates(array $coordinates): self
    {
        $this->coordinates = $coordinates;

        return $this;
    }

    public function getRental(): ?Rental
    {
        return $this->rental;
    }

    public function setRental(?Rental $rental): self
    {
        // unset the owning side of the relation if necessary
        if ($rental === null && $this->rental !== null) {
            $this->rental->setGeolocation(null);
        }

        // set the owning side of the relation if necessary
        if ($rental !== null && $rental->getGeolocation() !== $this) {
            $rental->setGeolocation($this);
        }

        $this->rental = $rental;

        return $this;
    }
}
