<?php

namespace App\Domain;

class Dimension
{
    protected int $height;
    protected int $width;

    public function __construct(int $height, int $width)
    {
        $this->height = $height;
        $this->width = $width;
    }

    final public function getHeight(): int
    {
        return $this->height;
    }

    final public function getWidth(): int
    {
        return $this->width;
    }

    final public function toArray(): array
    {
        return [
            'height' => $this->height,
            'width' => $this->width,
        ];
    }
}
