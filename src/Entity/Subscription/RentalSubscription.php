<?php

namespace App\Entity\Subscription;

use App\Domain\Price;
use App\Domain\Subscription\DiscountApplier;
use App\Domain\Subscription\SubscriptionStatus;
use App\Entity\IdentityTrait;
use App\Entity\Rental\Rental;
use App\Entity\Subscription\Traits\RentalSubscriptionAccessors;
use App\Entity\TimestampabbleTrait;
use App\Repository\Subscription\RentalSubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RentalSubscriptionRepository::class)]
class RentalSubscription
{
    use IdentityTrait, TimestampabbleTrait, RentalSubscriptionAccessors;

    #[ORM\ManyToOne(targetEntity: Subscription::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    private Subscription $subscription;

    #[ORM\ManyToOne(targetEntity: Rental::class, cascade: ['persist', 'remove'], inversedBy: 'subscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private Rental $rental;

    #[ORM\ManyToOne(targetEntity: Discount::class)]
    private ?Discount $discount;

    #[ORM\Column(type: 'price')]
    private Price $amount;

    #[ORM\Column(type: 'subscription_status')]
    private SubscriptionStatus $status;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $providerChargeId = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private \DateTimeInterface $paidAt;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $expiresAt = null;

    #[ORM\OneToOne(inversedBy: 'activeSubscription', targetEntity: Rental::class)]
    private ?Rental $activeRental = null;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isConsumed = false;

    final public static function new(Subscription $subscription, Rental $rental): self
    {
        return (new self())
            ->setStatus(SubscriptionStatus::DRAFT)
            ->setSubscription($subscription)
            ->setRental($rental)
            ->setDiscount(null)
            ->setAmount($subscription->getAmount())
            ->setCreatedAt(new \DateTime('now'))
            ->setUpdatedAt(new \DateTIme('now'))
        ;
    }

    final public function applyDiscount(DiscountApplier $applier, Discount $discount): self
    {
        if (null !== $this->discount && $discount === $this->discount) {
            return $this;
        }

        return $this
            ->setAmount($applier->apply($discount, $this->amount))
            ->setDiscount($discount)
        ;
    }

    final public function pay(Subscription $subscription, string $providerChargeId): self
    {
        return $this
            ->setProviderChargeId($providerChargeId)
            ->setStatus(SubscriptionStatus::ACTIVE)
            ->setPaidAt(new \DateTime('now'))
        ;
    }

}
