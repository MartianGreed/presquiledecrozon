<?php

namespace App\Domain\Rental\DTO;

/**
 * @phpstan-import-type Coordinates from GeolocationDTO
 *
 * @phpstan-type Bound array{south: float, west: float, north: float, east: float}
 * @phpstan-type AddressComponent array{long_name: string, short_name: string, types: array<int, string>}
 * @phpstan-type Geometry array{bounds: Bound, location: Coordinates, location_type: string, viewport: Bound}
 * @phpstan-type Meta array{address_components: array<AddressComponent>, formatted_address: string, geometry: Geometry, places_id: string, types: array<int, string>}
 */
final class LocationSuggestion
{
    /**
     * @var Meta|null
     */
    private ?array $meta;

    public function __construct(
        public ?string $suggestions,
        ?string $meta
    )
    {
        if ('' !== $meta && null !== $meta) {
            /** @var Meta $metaArray */
            $metaArray = json_decode($meta, true, 512, JSON_THROW_ON_ERROR);
            $this->meta = $metaArray;
        } else {
            $this->meta = null;
        }
    }

    public function hasSuggestions(): bool
    {
        return null !== $this->meta;
    }

    /**
     * @return ?Meta
     */
    public function getMeta(): ?array
    {
        return $this->meta;
    }
}
