<?php

namespace App\Entity\Subscription;

use App\Entity\IdentityTrait;
use App\Entity\TimestampabbleTrait;
use App\Entity\User;
use App\Repository\Subscription\DiscountRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiscountRepository::class)]
class Discount
{
    use IdentityTrait, TimestampabbleTrait;

    #[ORM\Column(type: 'string', length: 10)]
    private string $type;

    #[ORM\Column(type: 'price')]
    private int $amount;

    #[ORM\Column(type: 'string', length: 255)]
    private string $code;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $expiresAt;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'discounts')]
    private ?User $payee;

    final public function getType(): ?string
    {
        return $this->type;
    }

    final public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    final public function getAmount(): ?int
    {
        return $this->amount;
    }

    final public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    final public function getCode(): ?string
    {
        return $this->code;
    }

    final public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    final public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    final public function setExpiresAt(\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    final public function getPayee(): ?User
    {
        return $this->payee;
    }

    final public function setPayee(?User $payee): self
    {
        $this->payee = $payee;

        return $this;
    }
}
