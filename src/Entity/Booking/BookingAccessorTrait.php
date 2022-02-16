<?php

namespace App\Entity\Booking;

use App\Entity\Rental\Rental;
use App\Entity\User;

trait BookingAccessorTrait
{
    final public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    final public function setStartAt(\DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    final public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    final public function setEndAt(\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    final public function getPeopleCount(): ?int
    {
        return $this->peopleCount;
    }

    final public function setPeopleCount(int $peopleCount): self
    {
        $this->peopleCount = $peopleCount;

        return $this;
    }

    final public function getRental(): Rental
    {
        return $this->rental;
    }

    final public function setRental(Rental $rental): self
    {
        $this->rental = $rental;

        return $this;
    }

    final public function getBooker(): User
    {
        return $this->booker;
    }

    final public function setBooker(User $booker): self
    {
        $this->booker = $booker;

        return $this;
    }
}