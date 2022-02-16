<?php

namespace App\Entity\Booking;

use App\Entity\IdentityTrait;
use App\Entity\Rental\Rental;
use App\Entity\TimestampabbleTrait;
use App\Entity\User;
use App\Repository\Booking\BookingRepository;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\ManyToOne(targetEntity: Rental::class, inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private Rental $rental;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private User $booker;
}
