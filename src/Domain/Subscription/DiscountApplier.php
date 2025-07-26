<?php

namespace App\Domain\Subscription;

use App\Domain\Price;
use App\Entity\Subscription\Discount;

final class DiscountApplier
{
    public function apply(Discount $discount, Price $amount): Price
    {
        return match ($discount->getType()) {
            '€' => $this->applyFixDiscount((int) $discount->getAmount()->getAmount(), $amount),
            '%' => $this->applyPercentageDiscount((int) $discount->getAmount()->getAmount(), $amount),
            default => throw new \DomainException('Type '.$discount->getType().' is not implemented yet.'),
        };
    }

    private function applyFixDiscount(int $discountAmount, Price $amount): Price
    {
        return $amount->minus($discountAmount);
    }

    private function applyPercentageDiscount(int $discountAmount, Price $amount): Price
    {
        return $amount->minus(($discountAmount / 100) * $amount->getAmount());
    }
}
