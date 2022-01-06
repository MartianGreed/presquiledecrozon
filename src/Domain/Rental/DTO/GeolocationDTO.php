<?php

namespace App\Domain\Rental\DTO;

use App\Entity\Rental\Geolocation;

/**
 * @phpstan-type Coordinates array{lat: float, lng: float}
 * @phpstan-type Viewport array{northeast: Coordinates, southwest: Coordinates}
 * @phpstan-type Meta array{viewport?: Viewport, formatted_address?: string, place_id?: string}
 * @phpstan-type GeolocationDTOArray array{lat: float, lng: float, meta: Meta|array<string>}
 */
final class GeolocationDTO
{
    /**
     * @param Meta $meta
     */
    private function __construct(public float $lat, public float $lng, public readonly array $meta = [])
    {
    }

    /**
     * @param Meta $meta
     */
    public static function new(float $lat, float $lng, array $meta = []): self
    {
        return new self($lat, $lng, $meta);
    }

    /**
     * @psalm-return GeolocationDTOArray
     */
    public function toArray(): array
    {
        return [
            'lat'  => $this->lat,
            'lng'  => $this->lng,
            'meta' => $this->filterMeta(),
        ];
    }

    public static function fromEntity(Geolocation $geolocation): self
    {
        $coordinates = $geolocation->getCoordinates();


        $meta = [];
        if (array_key_exists('meta', $coordinates)) {
            /** @var Meta $meta */
            $meta = $coordinates['meta'];
        }

        return self::new((float)$coordinates['lat'], (float)$coordinates['lng'], $meta);
    }

    /** @return Meta */
    private function filterMeta(): array
    {
        if (0 >= count($this->meta)) {
            return [];
        }

        return [
            'viewport' => $this->meta['viewport'],
            'formatted_address' => $this->meta['formatted_address'],
            'place_id' => $this->meta['place_id'],
        ];
    }
}
