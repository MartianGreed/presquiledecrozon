<?php

namespace App\Domain;

final class Price implements \Stringable
{
    public function __construct(
        private readonly float $value,
        private readonly Currency $currency = new Euro(),
    ) {
    }

    public function getAmount(): float
    {
        return $this->value;
    }

    public function getValue(): int
    {
        return (int)($this->value * 100);
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function __toString(): string
    {
        /** @phpstan-ignore-next-line */
        return (\NumberFormatter::create('fr', \NumberFormatter::CURRENCY))
            ->formatCurrency($this->getValue() / 100, $this->currency->getValue())
        ;
    }

    public function add(float $add): Price
    {
        return new Price($this->value + $add, $this->currency);
    }

    public function minus(float $minus): Price
    {
        return new Price($this->value - $minus, $this->currency);
    }

    public function times(float $times): Price
    {
        return new Price($this->value * $times, $this->currency);
    }

    public function equals(Price $other): bool
    {
        return $this->getValue() === $other->getValue() && $this->currency->getICUValue() === $other->getCurrency()->getICUValue();
    }
}
