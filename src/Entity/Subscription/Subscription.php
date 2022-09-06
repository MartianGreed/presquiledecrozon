<?php

namespace App\Entity\Subscription;

use App\Domain\Price;
use App\Entity\Identity;
use App\Entity\IdentityTrait;
use App\Entity\TimestampabbleTrait;
use App\Repository\Subscription\SubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
class Subscription implements Identity
{
    use IdentityTrait, TimestampabbleTrait;

    #[ORM\Column(type: 'price')]
    private Price $amount;

    #[ORM\Column(type: 'string', length: 10)]
    private string $validityDuration;

    #[ORM\Column(type: 'string')]
    private string $name;

    final public function getAmount(): Price
    {
        return $this->amount;
    }

    final public function setAmount(Price $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    final public function getValidityDuration(): ?string
    {
        return $this->validityDuration;
    }

    final public function setValidityDuration(string $validityDuration): self
    {
        $this->validityDuration = $validityDuration;

        return $this;
    }

    final public function getName(): string
    {
        return $this->name;
    }

    final public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    final public function getSubscriptionExpirationDate(\DateTime $date): \DateTime
    {
        return $date->add(new \DateInterval($this->validityDuration));
    }
}
