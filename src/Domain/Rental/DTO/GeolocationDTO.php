<?php

namespace App\Domain\Rental\DTO;

final class GeolocationDTO
{
    /**
     * @param array<string, int> $meta
     */
    private function __construct(public readonly float $lat, public readonly float $lng, public readonly array $meta = [])
    {
    }

    /**
     * @param array<string, int> $meta
     */
    public static function new(float $lat, float $lng, array $meta = []): self
    {
        return new self($lat, $lng, $meta);
    }

    /**
     * @psalm-return array<string, array<string|int>|float>
     */
    public function toArray(): array
    {
        return [
            'lat' => $this->lat,
            'lng' => $this->lng,
            'meta' => $this->meta,
        ];
    }
}
