<?php

namespace App\Entity\Rental;

use App\Entity\IdentityTrait;
use App\Repository\Rental\ConditionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConditionRepository::class)]
class Condition
{
    use IdentityTrait;

    #[ORM\Column(type: 'boolean')]
    private bool $animalsAccepted = false;

    #[ORM\Column(type: 'boolean')]
    private bool $smokingAllowed = false;

    #[ORM\Column(type: 'array')]
    private array $additionnalRules = [];

    #[ORM\OneToOne(mappedBy: 'condition', targetEntity: Rental::class, cascade: ['persist', 'remove'])]
    private Rental $rental;

    public function getAnimalsAccepted(): ?bool
    {
        return $this->animalsAccepted;
    }

    public function setAnimalsAccepted(bool $animalsAccepted): self
    {
        $this->animalsAccepted = $animalsAccepted;

        return $this;
    }

    public function getSmokingAllowed(): ?bool
    {
        return $this->smokingAllowed;
    }

    public function setSmokingAllowed(bool $smokingAllowed): self
    {
        $this->smokingAllowed = $smokingAllowed;

        return $this;
    }

    public function getAdditionnalRules(): ?array
    {
        return $this->additionnalRules;
    }

    public function setAdditionnalRules(array $additionnalRules): self
    {
        $this->additionnalRules = $additionnalRules;

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
            $this->rental->setCondition(null);
        }

        // set the owning side of the relation if necessary
        if ($rental !== null && $rental->getCondition() !== $this) {
            $rental->setCondition($this);
        }

        $this->rental = $rental;

        return $this;
    }
}
