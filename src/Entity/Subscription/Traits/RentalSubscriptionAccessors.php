<?php

namespace App\Entity\Subscription\Traits;

use App\Domain\Price;
use App\Domain\Subscription\SubscriptionStatus;
use App\Entity\Rental\Rental;
use App\Entity\Subscription\Discount;
use App\Entity\Subscription\Subscription;

trait RentalSubscriptionAccessors
{
    final public function getSubscription(): Subscription
    {
        return $this->subscription;
    }

    final public function setSubscription(Subscription $subscription): self
    {
        $this->subscription = $subscription;

        return $this;
    }

    final public function getRental(): ?Rental
    {
        return $this->rental;
    }

    final public function setRental(Rental $rental): self
    {
        $this->rental = $rental;

        return $this;
    }

    final public function getDiscount(): ?Discount
    {
        return $this->discount;
    }

    final public function setDiscount(?Discount $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    final public function getAmount(): Price
    {
        return $this->amount;
    }

    final public function setAmount(Price $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    final public function getStatus(): SubscriptionStatus
    {
        return $this->status;
    }

    final public function setStatus(SubscriptionStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    final public function getProviderChargeId(): ?string
    {
        return $this->providerChargeId;
    }

    final public function setProviderChargeId(?string $providerChargeId): self
    {
        $this->providerChargeId = $providerChargeId;
        return $this;
    }

    final public function getPaidAt(): ?\DateTimeInterface
    {
        return $this->paidAt;
    }

    final public function setPaidAt(\DateTimeInterface $paidAt): self
    {
        $this->paidAt = $paidAt;

        return $this;
    }

    final public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    final public function setExpiresAt(\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    final public function isConsumed(): bool
    {
        return $this->isConsumed;
    }

    final public function setIsConsumed(bool $isConsumed): self
    {
        $this->isConsumed = $isConsumed;
        return $this;
    }

    final public function getActiveRental(): ?Rental
    {
        return $this->activeRental;
    }

    final public function setActiveRental(?Rental $rental): self
    {
        $this->activeRental = $rental;
        return $this;
    }
}
