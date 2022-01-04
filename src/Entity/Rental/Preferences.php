<?php

namespace App\Entity\Rental;

use App\Entity\IdentityTrait;
use App\Repository\Rental\PreferencesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PreferencesRepository::class)]
class Preferences
{
    use IdentityTrait;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $acceptedLastBooking;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $maxTimeBeforeBooking;

    #[ORM\Column(type: 'time')]
    private ?string $beginBookingAt;

    #[ORM\Column(type: 'time')]
    private ?string $endBookingAt;

    #[ORM\OneToOne(mappedBy: 'preferences', targetEntity: Rental::class, cascade: ['persist', 'remove'])]
    private Rental $rental;

    public function getAcceptedLastBooking(): ?string
    {
        return $this->acceptedLastBooking;
    }

    public function setAcceptedLastBooking(string $acceptedLastBooking): self
    {
        $this->acceptedLastBooking = $acceptedLastBooking;

        return $this;
    }

    public function getMaxTimeBeforeBooking(): ?string
    {
        return $this->maxTimeBeforeBooking;
    }

    public function setMaxTimeBeforeBooking(string $maxTimeBeforeBooking): self
    {
        $this->maxTimeBeforeBooking = $maxTimeBeforeBooking;

        return $this;
    }

    public function getBeginBookingAt(): ?\DateTimeInterface
    {
        return $this->beginBookingAt;
    }

    public function setBeginBookingAt(\DateTimeInterface $beginBookingAt): self
    {
        $this->beginBookingAt = $beginBookingAt;

        return $this;
    }

    public function getEndBookingAt(): ?\DateTimeInterface
    {
        return $this->endBookingAt;
    }

    public function setEndBookingAt(\DateTimeInterface $endBookingAt): self
    {
        $this->endBookingAt = $endBookingAt;

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
            $this->rental->setPreferences(null);
        }

        // set the owning side of the relation if necessary
        if ($rental !== null && $rental->getPreferences() !== $this) {
            $rental->setPreferences($this);
        }

        $this->rental = $rental;

        return $this;
    }
}
