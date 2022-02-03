<?php

namespace App\Entity\Data;

use App\Entity\IdentityTrait;
use App\Repository\Data\RentalTypeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RentalTypeRepository::class)]
class RentalType
{
    use IdentityTrait;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $label = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $value = null;

    public function __toString(): string
    {
        return $this->label ?? '';
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
