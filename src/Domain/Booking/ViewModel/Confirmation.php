<?php

namespace App\Domain\Booking\ViewModel;

use App\Domain\Booking\BookingPrices;
use App\Entity\Booking\Booking;
use App\Entity\Rental\Rental;

final class Confirmation
{
    private readonly Rental $rental;
    private readonly \DateTimeInterface $startAt;
    private readonly \DateTimeInterface $endAt;
    private readonly int $peopleCount;
    private readonly BookingPrices $prices;

    public function __construct(Booking $booking)
    {
        $startAt = $booking->getStartAt();
        $endAt = $booking->getEndAt();

        if (null === $startAt) {
            throw new \RuntimeException('Start date cannot be null');
        }

        if (null === $endAt) {
            throw new \RuntimeException('End date cannot be null');
        }

        $this->rental = $booking->getRental();
        $this->startAt = $startAt;
        $this->endAt = $endAt;
        $this->peopleCount = (int) $booking->getPeopleCount();
        $this->prices = $booking->getPrices();
    }

    public function getBookingDuration(): string
    {
        $diffInDays = $this->startAt->diff($this->endAt)->d + 1;
        $weeks = $diffInDays % 7;
        $leftDays = ($diffInDays - (7 * ($diffInDays % 7)) - 1);

        $str = '';
        if (0 < $weeks) {
            $str .= $weeks . ' semaine' . ($weeks > 1 ? 's' : '');
        }
        if (0 < $leftDays) {
            $str .= ' et ' . $leftDays . ' jour' . ($leftDays > 1 ? 's' : '');
        }

        return $str;
    }

    public function getRental(): Rental
    {
        return $this->rental;
    }

    public function getStartAt(): string
    {
        return $this->getFormattedDate($this->startAt);
    }

    public function getEndAt(): string
    {
        return $this->getFormattedDate($this->endAt);
    }

    private function getFormattedDate(\DateTimeInterface $dateTime): string
    {
        return '<span class="days">' . $dateTime->format('d') . '</span>' . $dateTime->format('M');
    }

    public function getRentalTitle(): string
    {
        return sprintf(
            '%s de %s',
            $this->rental->getConfiguration()?->getType()?->getLabel(),
            $this->rental->getOwner()?->getProfile()?->getFirstname()
        );
    }

    public function getRentalLocation(): string
    {
        return (string) $this->rental->getAddress()?->getTown()?->getName();
    }

    public function getPeopleCount(): int
    {
        return $this->peopleCount;
    }

    public function getTotalPrice(): string
    {
        return $this->prices->getTotalPrice();
    }

    public function getDefaultMessageForOwner(): string
    {
        $message = 'Bonjour %s, je souhaiterai réserver votre logement pour la période du %s au %s ! Bonne journée :)';

        return sprintf(
            $message,
            $this->rental->getOwner()?->getProfile()?->getFirstname(),
            $this->startAt->format('d M'),
            $this->endAt->format('d M')
        );
    }
}
