<?php

namespace App\Domain\Booking;

use App\Domain\Price;

/**
 * @phpstan-type PriceItem array{count: int, price: Price}
 */
final class BookingPrices
{
    /** @var array<PriceItem> */
    private array $prices = [];

    public function addPrice(Price $weekly, Price $daily, int $daysCount): self
    {
        if (7 <= $daysCount) {
            $this->prices[] = [
                'count' => $daysCount % 7,
                'price' => $weekly,
            ];
            $this->prices[] = [
                'count' => $daysCount - (7 * ($daysCount % 7)),
                'price' => $daily,
            ];
            return $this;
        }

        $this->prices[] = [
            'count' => $daysCount,
            'price' => $daily,
        ];

        return $this;
    }

    /** @return array<PriceItem> */
    public function getPrices(): array
    {
        return $this->prices;
    }

    public function getTotalPrice(): Price
    {
        $totalPrice = new Price(0);
        foreach ($this->prices as $price) {
            $tmpPrice = $price['price']->times($price['count']);
            $totalPrice = $totalPrice->add($tmpPrice->getAmount());
        }
        return $totalPrice;
    }

    public function __toString(): string
    {
        return json_encode(array_map(static fn(array $data) => ['count' => $data['count'], 'price' => $data['price']->getAmount()], $this->prices), JSON_THROW_ON_ERROR);
    }

    /** @param array<PriceItem> $prices */
    private function setPrices(array $prices): self
    {
        $this->prices = $prices;
        return $this;
    }

    /** @param array<array{count: int, price: float}> $data */
    public static function fromArray(array $data): self
    {
        return (new self())->setPrices(array_map(static fn($i) => ['count' => $i['count'], 'price' => new Price($i['price'])], $data));
    }
}