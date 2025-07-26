<?php

namespace App\Entity\Data;

use App\Entity\Identity;
use App\Entity\IdentityTrait;
use App\Repository\Data\LinensCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LinensCategoryRepository::class)]
class LinensCategory implements Identity
{
    use IdentityTrait;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    /**
     * @var ArrayCollection<int, Linens>
     */
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Linens::class)]
    private Collection $linens;

    public function __construct()
    {
        $this->linens = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @psalm-return ArrayCollection<int, Linens>
     */
    public function getLinens(): Collection
    {
        return $this->linens;
    }

    public function addLinen(Linens $linen): self
    {
        if (! $this->linens->contains($linen)) {
            $this->linens[] = $linen;
            $linen->setCategory($this);
        }

        return $this;
    }

    public function removeLinen(Linens $linen): self
    {
        $this->linens->removeElement($linen);

        return $this;
    }
}
