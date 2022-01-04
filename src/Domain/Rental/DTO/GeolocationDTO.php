<?php

namespace App\Domain\Rental\DTO;

final class GeolocationDTO
{
    public readonly float $lat;
    public readonly float $lng;
    public readonly array $meta;

    private function __construct(float $lat, float $lng, array $meta = [])
    {
        $this->lat = $lat;
        $this->lng = $lng;
        $this->meta = $meta;
    }

    public static function new(float $lat, float $lng, array $meta = []): self
    {
        return new self($lat, $lng, $meta);
    }

    public function toArray(): array
    {
        return [
            'lat' => $this->lat,
            'lng' => $this->lng,
            'meta' => $this->meta,
        ];
    }
}