<?php

namespace App\Entity\Rental;

use App\Entity\Data\RentalType;
use App\Entity\IdentityTrait;
use App\Repository\Rental\ConfigurationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConfigurationRepository::class)]
class Configuration
{
    use IdentityTrait;

    #[ORM\ManyToOne(targetEntity: RentalType::class)]
    #[ORM\JoinColumn(nullable: false)]
    private RentalType $type;

    #[ORM\Column(type: 'integer')]
    private ?int $peopleCount;

    #[ORM\OneToMany(mappedBy: 'configuration', targetEntity: Bedroom::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $bedrooms;

    #[ORM\OneToOne(inversedBy: 'configuration', targetEntity: Rental::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private Rental $rental;

    public function __construct()
    {
        $this->bedrooms = new ArrayCollection();
    }

    public function getType(): ?RentalType
    {
        return $this->type;
    }

    public function setType(?RentalType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPeopleCount(): ?int
    {
        return $this->peopleCount;
    }

    public function setPeopleCount(int $peopleCount): self
    {
        $this->peopleCount = $peopleCount;

        return $this;
    }

    /**
     * @return Collection|Bedroom[]
     */
    public function getBedrooms(): Collection
    {
        return $this->bedrooms;
    }

    public function addBedroom(Bedroom $bedroom): self
    {
        if (!$this->bedrooms->contains($bedroom)) {
            $this->bedrooms[] = $bedroom;
            $bedroom->setConfiguration($this);
        }

        return $this;
    }

    public function removeBedroom(Bedroom $bedroom): self
    {
        if ($this->bedrooms->removeElement($bedroom)) {
            // set the owning side to null (unless already changed)
            if ($bedroom->getConfiguration() === $this) {
                $bedroom->setConfiguration(null);
            }
        }

        return $this;
    }

    public function getRental(): ?Rental
    {
        return $this->rental;
    }

    public function setRental(Rental $rental): self
    {
        $this->rental = $rental;

        return $this;
    }
}
