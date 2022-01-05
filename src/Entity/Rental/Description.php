<?php

namespace App\Entity\Rental;

use App\Entity\IdentityTrait;
use App\Repository\Rental\DescriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DescriptionRepository::class)]
class Description
{
    use IdentityTrait;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\OneToOne(mappedBy: 'description', targetEntity: Rental::class, cascade: ['persist', 'remove'])]
    private Rental $rental;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getRental(): ?Rental
    {
        return $this->rental;
    }

    public function setRental(Rental $rental): self
    {
        if ($rental->getDescription() !== $this) {
            $rental->setDescription($this);
        }

        $this->rental = $rental;

        return $this;
    }
}
