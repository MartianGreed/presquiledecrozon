<?php

namespace App\Domain\Rental;

use App\Domain\Dimension;

final class BedSize extends Dimension
{
    public function __toString(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    public function getDimensions(): string
    {
        return sprintf('%dx%d cm', $this->height, $this->width);
    }
}