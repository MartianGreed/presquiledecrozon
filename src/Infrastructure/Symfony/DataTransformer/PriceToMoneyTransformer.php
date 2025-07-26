<?php

namespace App\Infrastructure\Symfony\DataTransformer;

use App\Domain\Price;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<Price|null, float|null>
 */
final class PriceToMoneyTransformer implements DataTransformerInterface
{
    /**
     * @param ?Price $value
     */
    public function transform(mixed $value): ?float
    {
        if (null === $value) {
            return null;
        }

        return $value->getAmount();
    }

    /**
     * @param float $value
     */
    public function reverseTransform(mixed $value): Price
    {
        return new Price($value);
    }
}
