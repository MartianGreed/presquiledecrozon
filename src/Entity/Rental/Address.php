<?php

namespace App\Entity\Rental;

use App\Entity\Data\Town;
use App\Entity\IdentityTrait;
use App\Repository\Rental\AddressRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address
{
    use IdentityTrait;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $address = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $address2 = null;

    #[ORM\ManyToOne(targetEntity: Town::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Town $town = null;

    #[ORM\OneToOne(mappedBy: 'address', targetEntity: Rental::class, cascade: ['persist', 'remove'])]
    private Rental $rental;


    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setAddress2(?string $address2): self
    {
        $this->address2 = $address2;

        return $this;
    }

    public function getTown(): ?Town
    {
        return $this->town;
    }

    public function setTown(?Town $town): self
    {
        $this->town = $town;

        return $this;
    }

    public function getRental(): ?Rental
    {
        return $this->rental;
    }

    public function setRental(Rental $rental): self
    {
        if ($rental->getAddress() !== $this) {
            $rental->setAddress($this);
        }

        $this->rental = $rental;

        return $this;
    }
}
