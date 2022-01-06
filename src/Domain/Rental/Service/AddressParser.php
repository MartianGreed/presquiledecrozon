<?php

namespace App\Domain\Rental\Service;

/**
 * @phpstan-type AddressArray array{address: string, postal_code: string, town: string, country: string}
 */
final class AddressParser
{
    /** @return AddressArray */
    public static function getParts(string $strAddress): array
    {
        $parts = explode(', ', $strAddress);
        $address = array_shift($parts);
        $country = array_pop($parts);

        [$postalCode, $town] = explode(' ', $parts[0]);

        return [
            'address' => (string) $address,
            'postal_code' => $postalCode,
            'town' => $town,
            'country' => (string) $country,
        ];
    }
}