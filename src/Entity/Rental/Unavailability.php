<?php

namespace App\Entity\Rental;

use App\Entity\Identity;
use App\Entity\IdentityTrait;
use App\Repository\Rental\UnavailabilityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UnavailabilityRepository::class)]
class Unavailability implements \Stringable, Identity
{
    use IdentityTrait;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $startAt = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $endAt = null;

    #[ORM\ManyToOne(targetEntity: Rental::class, inversedBy: 'unavailabilities')]
    #[ORM\JoinColumn(nullable: false)]
    private Rental $rental;

    public function __toString(): string
    {
        return sprintf('du %s au %s', $this->startAt?->format('d/m/Y'), $this->endAt?->format('d/m/Y'));
    }

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getRental(): Rental
    {
        return $this->rental;
    }

    public function setRental(Rental $rental): self
    {
        $this->rental = $rental;

        return $this;
    }

    /** @return array{start: string, end: string} */
    final public function toArray(): array
    {
        return [
            'start' => (string) $this->startAt?->format('d/m/Y'),
            'end' => (string) $this->endAt?->format('d/m/Y'),
        ];
    }
}
