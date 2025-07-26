<?php

namespace App\Tests\Unit\App\Entity\Rental;

use App\Domain\Booking\BookingPrices;
use App\Domain\Booking\BookingRequest;
use App\Domain\Price as PriceVO;
use App\Entity\Rental\Price;
use App\Entity\Rental\Rental;
use PHPUnit\Framework\TestCase;

final class RentalTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('rangesProvider')]
    public function testGetPricesForRangeReturnsCorrectPrices(string $startAt, string $endAt, int $peopleCount, BookingPrices $expected): void
    {
        $rental = static::createRental();

        $bookingRequest = BookingRequest::fromArray([
            'start_at' => $startAt,
            'end_at' => $endAt,
            'people_count' => $peopleCount,
            'rental' => $rental,
        ]);

        $prices = $rental->getPricesForRange($bookingRequest->startAt, $bookingRequest->endAt);

        self::assertCount(count($expected->getPrices()), $prices->getPrices());

        $count = count($prices->getPrices());
        for ($i = 0; $i < $count; $i++) {
            $actual = $prices->getPrices()[$i];
            $expectedPrices = $expected->getPrices()[$i];

            self::assertSame($expectedPrices['count'], $actual['count']);
            self::assertTrue($expectedPrices['price']->equals($actual['price']));
        }
    }

    /** @return array<array{0: string, 1: string, 2: int, 3: BookingPrices}> */
    public static function rangesProvider(): array
    {
        return [
            [
                '2022-02-16', '2022-02-23', 3,
                (new BookingPrices())->addPrice(new PriceVO(635), new PriceVO(66), 7),
            ],
            [
                '2022-06-13', '2022-06-17', 1,
                (new BookingPrices())
                    ->addPrice(new PriceVO(635), new PriceVO(66), 2)
                    ->addPrice(new PriceVO(1000), new PriceVO(100), 2),
            ],
            [
                '2022-07-13', '2022-07-17', 1,
                (new BookingPrices())
                    ->addPrice(new PriceVO(1000), new PriceVO(100), 2)
                    ->addPrice(new PriceVO(1200), new PriceVO(120), 2),
            ],
        ];
    }

    private static function createRental(): Rental
    {
        $rental = new Rental();
        $rental
            ->addPrice(
                (new Price())
                    ->setRangeStart(new \DateTime('2022-06-15'))
                    ->setRangeEnd(new \DateTime('2022-07-15'))
                    ->setWeeklyRate(new PriceVO(1000))
                    ->setDailyRate(new PriceVO(100))
            )
            ->addPrice(
                (new Price())
                    ->setRangeStart(new \DateTime('2022-07-15'))
                    ->setRangeEnd(new \DateTime('2022-08-15'))
                    ->setWeeklyRate(new PriceVO(1200))
                    ->setDailyRate(new PriceVO(120))
            )
            ->addPrice(
                (new Price())
                    ->setRangeStart(new \DateTime('2022-08-15'))
                    ->setRangeEnd(new \DateTime('2022-09-15'))
                    ->setWeeklyRate(new PriceVO(1000))
                    ->setDailyRate(new PriceVO(100))
            )
        ;

        $rental->setWeeklyRate(new PriceVO(635))->setDailyRate(new PriceVO(66));

        return $rental;
    }
}
