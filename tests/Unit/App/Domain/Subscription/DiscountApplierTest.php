<?php

namespace App\Tests\Unit\App\Domain\Subscription;

use App\Domain\Price;
use App\Domain\Subscription\DiscountApplier;
use App\Entity\Subscription\Discount;
use PHPUnit\Framework\TestCase;

final class DiscountApplierTest extends TestCase
{
    private DiscountApplier $applier;

    public function setUp(): void
    {
        $this->applier = new DiscountApplier();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideDiscount')]
    public function testItProperlyAppliesDiscount(Discount $discount, float $amount, Price $expected): void
    {
        self::assertSame($expected->getValue(), $this->applier->apply($discount, new Price($amount))->getValue());
    }

    /**
     * @return array<array{Discount, int, Price}>
     */
    public static function provideDiscount(): array
    {
        return [
            [self::createDiscount('€', 10), 89, new Price(79)],
            [self::createDiscount('%', 10), 89, new Price(80.1)],
        ];
    }

    private static function createDiscount(string $type, int $amount): Discount
    {
        return (new Discount())->setType($type)->setAmount(new Price($amount));
    }
}
