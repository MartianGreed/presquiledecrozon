<?php

namespace App\Domain\Booking;

use App\Entity\Rental\Rental;

final class BookingRequest
{
    public function __construct(
        public readonly Rental $rental,
        public readonly \DateTimeInterface $startAt,
        public readonly \DateTimeInterface $endAt,
        public readonly int $peopleCount
    ) {
    }

    /**
     * @param array{start_at: string, end_at: string, people_count: int, rental: Rental} $data
     */
    public static function fromArray(array $data): self
    {
        $startAt = \DateTime::createFromFormat('Y-m-d', $data['start_at']);
        $endAt = \DateTime::createFromFormat('Y-m-d', $data['end_at']);

        if (!$startAt) {
            throw new \RuntimeException('Invalid start date provided');
        }
        if (!$endAt) {
            throw new \RuntimeException('Invalid end date provided');
        }

        return new self(
            $data['rental'],
            $startAt,
            $endAt,
            $data['people_count'],
        );
    }
}
