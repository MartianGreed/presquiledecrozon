<?php

namespace App\Entity\Rental;

use App\Entity\IdentityTrait;
use App\Repository\PriceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PriceRepository::class)]
class Price
{
    use IdentityTrait;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $rangeStart = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $rangeEnd = null;

    #[ORM\Column(type: 'integer')]
    private ?int $weeklyRate = null;

    #[ORM\Column(type: 'integer')]
    private ?int $dailyRate = null;

    #[ORM\ManyToOne(targetEntity: Rental::class, inversedBy: 'prices')]
    #[ORM\JoinColumn(nullable: false)]
    private Rental $rental;

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

    public function getWeeklyRate(): ?int
    {
        return $this->weeklyRate;
    }

    public function setWeeklyRate(int $weeklyRate): self
    {
        $this->weeklyRate = $weeklyRate;

        return $this;
    }

    public function getDailyRate(): ?int
    {
        return $this->dailyRate;
    }

    public function setDailyRate(int $dailyRate): self
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
