<?php

namespace App\Tests\Unit\App\Domain;

use App\Domain\Price;
use PHPUnit\Framework\TestCase;

final class PriceTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('providePrices')]
    public function testItHasValuesAndAmount(float $base, int $value, float $amount, string $strVal): void
    {
        $price = new Price($base);

        self::assertSame($value, $price->getValue());
        self::assertSame($amount, $price->getAmount());
        self::assertSame($strVal, (string) $price);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideAdd')]
    public function testAdd(float $base, float $add, int $expected): void
    {
        $price = new Price($base);

        self::assertSame($expected, $price->add($add)->getValue());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideMinus')]
    public function testMinus(float $base, float $minus, int $expected): void
    {
        $price = new Price($base);
        self::assertSame($expected, $price->minus($minus)->getValue());
    }

    /** @return array<array{float, int, float, string}> */
    public static function providePrices(): array
    {
        return [
           [89, 8900, 89.0, '89,00 €'],
           [75.0, 7500, 75.0, '75,00 €'],
           [15.1333, 1513, 15.1333, '15,13 €'],
           [50.512, 5051, 50.512, '50,51 €'],
        ];
    }
    /** @return array<array{float, float, int}> */
    public static function provideAdd(): array
    {
        return [
            [89, 10, 9900],
            [75, .5, 7550],
            [89.33, .33, 8966],
            [89.54322, .54, 9008],
        ];
    }

    /** @return array<array{float, float, int}> */
    public static function provideMinus(): array
    {
        return [
            [89, 10, 7900],
            [75, .5, 7450],
            [89.33, .33, 8900],
            [89.54322, .54, 8900],
        ];
    }
}
