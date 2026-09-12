<?php

namespace App\Entity\Rental;

use App\Domain\Price as PriceVO;
use App\Entity\Identity;
use App\Entity\IdentityTrait;
use App\Repository\PriceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PriceRepository::class)]
class Price implements \Stringable, Identity
{
    use IdentityTrait;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $rangeStart = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $rangeEnd = null;

    #[ORM\Column(type: 'price')]
    private ?PriceVO $weeklyRate = null;

    #[ORM\Column(type: 'price')]
    private ?PriceVO $dailyRate = null;

    #[ORM\ManyToOne(targetEntity: Rental::class, inversedBy: 'prices')]
    #[ORM\JoinColumn(nullable: false)]
    private Rental $rental;

    public function __toString(): string
    {
        return sprintf('du %s au %s', $this->rangeStart?->format('d/m/Y'), $this->rangeEnd?->format('d/m/Y'));
    }

    public function getRangeStart(): ?\DateTimeInterface
    {
        return $this->rangeStart;
    }

    public function setRangeStart(\DateTimeInterface $rangeStart): self
    {
        $this->rangeStart = $rangeStart;

        return $this;
    }

    public function getRangeEnd(): ?\DateTimeInterface
    {
        return $this->rangeEnd;
    }

    public function setRangeEnd(\DateTimeInterface $rangeEnd): self
    {
        $this->rangeEnd = $rangeEnd;

        return $this;
    }

    public function getWeeklyRate(): ?PriceVO
    {
        return $this->weeklyRate;
    }

    public function setWeeklyRate(PriceVO $weeklyRate): self
    {
        $this->weeklyRate = $weeklyRate;

        return $this;
    }

    public function getDailyRate(): ?PriceVO
    {
        return $this->dailyRate;
    }

    public function setDailyRate(PriceVO $dailyRate): self
    {
        $this->dailyRate = $dailyRate;

        return $this;
    }

    public function getRental(): Rental
    {
        return $this->rental;
    }

    public function setRental(Rental $rental): self
    {
        $this->rental = $rental;

        return $this;
    }
}
