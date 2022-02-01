<?php

namespace App\Domain\Subscription;

use App\Entity\Subscription\RentalSubscription;
use App\Repository\Subscription\DiscountRepository;
use App\Repository\Subscription\RentalSubscriptionRepository;

final class DiscountService
{
    public function __construct(
        private readonly DiscountRepository $discountRepository,
        private readonly RentalSubscriptionRepository $rentalSubscriptionRepository,
    )
    {
    }

    public function applyDiscountCode(RentalSubscription $rentalSubscription, string $discountCode): RentalSubscription
    {
        $discount = $this->discountRepository->findOneBy(['code' => $discountCode]);
        if (null === $discount) {
            throw new \DomainException('Discount code cannot be null.');
        }

        $rentalSubscription = $rentalSubscription->applyDiscount(new DiscountApplier(), $discount);
        $this->rentalSubscriptionRepository->flush();

        return $rentalSubscription;
    }
}