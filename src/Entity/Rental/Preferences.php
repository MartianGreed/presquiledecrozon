<?php

namespace App\Entity\Rental;

use App\Entity\Identity;
use App\Entity\IdentityTrait;
use App\Repository\Rental\PreferencesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PreferencesRepository::class)]
class Preferences implements Identity
{
    use IdentityTrait;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $acceptedLastBooking = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $maxTimeBeforeBooking = null;

    #[ORM\Column(type: 'string')]
    private ?string $beginBookingAt = null;

    #[ORM\Column(type: 'string')]
    private ?string $endBookingAt = null;

    #[ORM\OneToOne(mappedBy: 'preferences', targetEntity: Rental::class, cascade: ['persist', 'remove'])]
    private Rental $rental;

    final public function getAcceptedLastBooking(): ?string
    {
        return $this->acceptedLastBooking;
    }

    final public function setAcceptedLastBooking(string $acceptedLastBooking): self
    {
        $this->acceptedLastBooking = $acceptedLastBooking;

        return $this;
    }

    final public function getMaxTimeBeforeBooking(): ?string
    {
        return $this->maxTimeBeforeBooking;
    }

    final public function setMaxTimeBeforeBooking(string $maxTimeBeforeBooking): self
    {
        $this->maxTimeBeforeBooking = $maxTimeBeforeBooking;

        return $this;
    }

    final public function getBeginBookingAt(): ?string
    {
        return $this->beginBookingAt;
    }

    final public function setBeginBookingAt(string $beginBookingAt): self
    {
        $this->beginBookingAt = $beginBookingAt;

        return $this;
    }

    final public function getEndBookingAt(): ?string
    {
        return $this->endBookingAt;
    }

    final public function setEndBookingAt(string $endBookingAt): self
    {
        $this->endBookingAt = $endBookingAt;

        return $this;
    }

    final public function getRental(): ?Rental
    {
        return $this->rental;
    }

    final public function setRental(Rental $rental): self
    {
        if ($rental->getPreferences() !== $this) {
            $rental->setPreferences($this);
        }

        $this->rental = $rental;

        return $this;
    }
}
