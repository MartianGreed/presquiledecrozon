<?php

namespace App\Entity\Booking;

use App\Domain\Booking\BookingPrices;
use App\Domain\Booking\Status;
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

    final public function getPrices(): BookingPrices
    {
        return $this->prices;
    }

    final public function setPrices(BookingPrices $prices): self
    {
        $this->prices = $prices;
        return $this;
    }

    final public function getStatus(): Status
    {
        return $this->status;
    }

    final public function setStatus(Status $status): self
    {
        $this->status = $status;
        return $this;
    }

    final public function getPeriod(): string
    {
        return sprintf(
            'Du %s au %s',
            $this->startAt->format('d/m/Y'),
            $this->endAt->format('d/m/Y')
        );
    }
}