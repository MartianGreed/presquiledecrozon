<?php

namespace App\Entity\Rental;

use App\Entity\Identity;
use App\Entity\IdentityTrait;
use App\Repository\Rental\ConditionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConditionRepository::class)]
class Condition implements Identity
{
    use IdentityTrait;

    #[ORM\Column(type: 'boolean')]
    private bool $animalsAccepted = false;

    #[ORM\Column(type: 'boolean')]
    private bool $smokingAllowed = false;

    /** @var array<int, string> */
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

    /** @psalm-return array<int, string>  */
    public function getAdditionnalRules(): ?array
    {
        return $this->additionnalRules;
    }

    /** @param array<int, string> $additionnalRules */
    public function setAdditionnalRules(array $additionnalRules): self
    {
        $this->additionnalRules = $additionnalRules;

        return $this;
    }

    public function getRental(): ?Rental
    {
        return $this->rental;
    }

    public function setRental(Rental $rental): self
    {
        if ($rental->getCondition() !== $this) {
            $rental->setCondition($this);
        }

        $this->rental = $rental;

        return $this;
    }
}
