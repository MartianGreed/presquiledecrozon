<?php

namespace App\Domain;

class Currency
{
    public function __construct(
        private readonly string $value,
        private readonly string $symbol
    )
    {
    }

    final public function getValue(): string
    {
        return $this->value;
    }

    final public function getSymbol(): string
    {
        return $this->symbol;
    }

    final public function getICUValue(): string
    {
        return strtoupper($this->value);
    }
}
