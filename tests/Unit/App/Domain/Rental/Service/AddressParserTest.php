<?php

namespace App\Tests\Unit\App\Domain\Rental\Service;

use App\Domain\Rental\Service\AddressParser;
use PHPUnit\Framework\TestCase;

class AddressParserTest extends TestCase
{
    /**
     * @param array<string, string> $addressParts
     * @dataProvider addressProvider
     */
    public function testItCanProperlyParseAddress(string $formattedAddress, array $addressParts): void
    {
        self::assertAddressIsCorrectlyParsed(AddressParser::getParts($formattedAddress), $addressParts);
    }


    /** @return array<int, array<int, array<string, string>|string>> */
    public function addressProvider(): array
    {
        return [
            ['Lézargol, 29560 Argol, France', ['address' => 'Lézargol', 'postal_code' => '29560', 'town' => 'Argol', 'country' => 'France']],
            ['25 place Grégoire Bordillon, 49100 Angers, France', ['address' => '25 place Grégoire Bordillon', 'postal_code' => '49100', 'town' => 'Angers', 'country' => 'France']],
            ['20 All. des Mouettes, 29560 Telgruc-sur-Mer, France', ['address' => '20 All. des Mouettes', 'postal_code' => '29560', 'town' => 'Telgruc-sur-Mer', 'country' => 'France']],
            ['11 Rue du Menhir, 29160 Crozon, France', ['address' => '11 Rue du Menhir', 'postal_code' => '29160', 'town' => 'Crozon', 'country' => 'France']],
            ['3 Rue du Mur, 29160 Crozon, France', ['address' => '3 Rue du Mur', 'postal_code' => '29160', 'town' => 'Crozon', 'country' => 'France']],
            ['4 Rue du Château d\'Eau, 29570 Camaret-sur-Mer, France', ['address' => '4 Rue du Château d\'Eau', 'postal_code' => '29570', 'town' => 'Camaret-sur-Mer', 'country' => 'France']],
        ];
    }

    /**
     * @param array<string, string> $parsedParts
     * @param array<string, string> $addressParts
     */
    private static function assertAddressIsCorrectlyParsed(array $parsedParts, array $addressParts): void
    {
        self::assertArrayHasKey('address', $parsedParts);
        self::assertArrayHasKey('postal_code', $parsedParts);
        self::assertArrayHasKey('town', $parsedParts);
        self::assertArrayHasKey('country', $parsedParts);

        self::assertSame($parsedParts['address'], $addressParts['address']);
        self::assertSame($parsedParts['postal_code'], $addressParts['postal_code']);
        self::assertSame($parsedParts['town'], $addressParts['town']);
        self::assertSame($parsedParts['country'], $addressParts['country']);
    }
}
