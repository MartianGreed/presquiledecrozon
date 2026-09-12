<?php

namespace App\Domain;

class Dimension
{
    public function __construct(
        protected int $height,
        protected int $width
    )
    {
    }

    final public function getHeight(): int
    {
        return $this->height;
    }

    final public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return array{height: int, width: int}
     */
    final public function toArray(): array
    {
        return [
            'height' => $this->height,
            'width' => $this->width,
        ];
    }
}
