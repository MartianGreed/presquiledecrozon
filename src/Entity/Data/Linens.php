<?php

namespace App\Entity\Data;

use App\Repository\Data\LinensRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: LinensRepository::class)]
class Linens
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $label = null;

    #[ORM\ManyToOne(targetEntity: LinensCategory::class, inversedBy: 'linens')]
    #[ORM\JoinColumn(nullable: false)]
    private LinensCategory $category;

    public function getId(): ?string
    {
        return $this->id;
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

    public function getCategory(): LinensCategory
    {
        return $this->category;
    }

    public function setCategory(LinensCategory $category): self
    {
        $this->category = $category;

        return $this;
    }
}
