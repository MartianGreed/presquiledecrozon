<?php

namespace App\Entity\Data;

use App\Entity\Identity;
use App\Entity\IdentityTrait;
use App\Repository\Data\LinensRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LinensRepository::class)]
class Linens implements \Stringable, Identity
{
    use IdentityTrait;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $label = null;

    #[ORM\ManyToOne(targetEntity: LinensCategory::class, inversedBy: 'linens')]
    #[ORM\JoinColumn(nullable: false)]
    private LinensCategory $category;


    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getCategory(): LinensCategory
    {
        return $this->category;
    }

    public function setCategory(LinensCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function __toString(): string
    {
        return $this->label ?? '';
    }
}
