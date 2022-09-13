<?php

namespace App\Entity\Booking;

use App\Domain\Booking\BookingPrices;
use App\Domain\Booking\BookingValidator;
use App\Domain\Booking\Exception\CannotBookOwnRentalException;
use App\Domain\Booking\Exception\CannotManagerOtherOwnersRentalException;
use App\Domain\Booking\Status;
use App\Entity\Identity;
use App\Entity\IdentityTrait;
use App\Entity\Rental\Rental;
use App\Entity\TimestampabbleTrait;
use App\Entity\User;
use App\Repository\Booking\BookingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: BookingRepository::class)]
class Booking implements Identity
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

    #[ORM\Column(type: 'booking_prices', nullable: true)]
    private BookingPrices $prices;

    #[ORM\ManyToOne(targetEntity: Rental::class, inversedBy: 'bookings', fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    private Rental $rental;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private User $booker;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $bookedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $confirmedAt = null;

    public static function init(
        BookingValidator $bookingValidator,
        Rental $rental,
        User $booker,
        \DateTimeInterface $startAt,
        \DateTimeInterface $endAt,
        int $peopleCount
    ): self {
        if ($booker->getId() === $rental->getOwner()?->getId()) {
            throw new CannotBookOwnRentalException();
        }
        // Let error bubble to the upper layer.
        $bookingValidator->validateBooking($rental, $startAt, $endAt, $peopleCount);

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
        $this->confirmedAt = new \DateTime();

        return $this;
    }

    final public function confirmBooking(User $owner, \DateTime $bookedAt): self
    {
        if ($owner->getId() !== $this->rental->getOwner()?->getId()) {
            throw new CannotManagerOtherOwnersRentalException();
        }
        $this->status = Status::BOOKED;
        $this->bookedAt = $bookedAt;

        return $this;
    }

    public function cancelBooking(User $owner, \DateTime $cancelledAt): self
    {
        if ($owner->getId() !== $this->rental->getOwner()?->getId()) {
            throw new CannotManagerOtherOwnersRentalException();
        }

        $this->status = Status::CANCELED;
        $this->bookedAt = null;
        $this->updatedAt = $cancelledAt;

        return $this;
    }

    public function cancel(\DateTime $cancelledAt): void
    {
        $this->status = Status::CANCELED;
        $this->bookedAt = null;
        $this->updatedAt = $cancelledAt;
    }

    public static function expiringInterval(): \DateInterval
    {
        return new \DateInterval('P3D');
    }
}
