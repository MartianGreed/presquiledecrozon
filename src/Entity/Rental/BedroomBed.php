<?php

namespace App\Entity\Rental;

use App\Entity\Data\Bed;
use App\Entity\Identity;
use App\Entity\IdentityTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
class BedroomBed implements Identity
{
    use IdentityTrait;

    #[ORM\ManyToOne(targetEntity: Bedroom::class, inversedBy: 'beds')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Bedroom $bedroom;

    #[ORM\ManyToOne(targetEntity: Bed::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Bed $bed;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private int $count = 0;

    public static function new(Bedroom $bedroom, Bed $bed, int $count): self
    {
        $self = new self();
        $self->bedroom = $bedroom;
        $self->bed = $bed;
        $self->count = $count;
        return $self;
    }

    public function getBedroom(): ?Bedroom
    {
        return $this->bedroom;
    }

    public function setBedroom(?Bedroom $bedroom): self
    {
        $this->bedroom = $bedroom;
        return $this;
    }

    public function getBed(): Bed
    {
        return $this->bed;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
