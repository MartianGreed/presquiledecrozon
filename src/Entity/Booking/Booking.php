<?php

namespace App\Entity\Booking;

use App\Domain\Booking\BookingPrices;
use App\Domain\Booking\Status;
use App\Entity\IdentityTrait;
use App\Entity\Rental\Rental;
use App\Entity\TimestampabbleTrait;
use App\Entity\User;
use App\Repository\Booking\BookingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: BookingRepository::class)]
class Booking
{
    use IdentityTrait, TimestampabbleTrait, BookingAccessorTrait;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $startAt;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $endAt;

    #[ORM\Column(type: 'integer')]
    private int $peopleCount;

    #[ORM\Column(type: 'booking_status')]
    private Status $status;

    #[ORM\Column(type: 'booking_prices')]
    private BookingPrices $prices;

    #[ORM\ManyToOne(targetEntity: Rental::class, inversedBy: 'bookings', fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    private Rental $rental;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private User $booker;

    public static function init(
        Rental $rental,
        User $booker,
        \DateTimeInterface $startAt,
        \DateTimeInterface $endAt,
        int $peopleCount
    ): self
    {
        return (new self())->setRental($rental)
            ->setBooker($booker)
            ->setStartAt($startAt)
            ->setEndAt($endAt)
            ->setPeopleCount($peopleCount)
            ->setStatus(Status::INITIALISED)
        ;
    }

    final public function confirm(int $peopleCount): self
    {
        $this->peopleCount = $peopleCount;
        $this->status = Status::CONFIRMED;

        return $this;
    }
}
