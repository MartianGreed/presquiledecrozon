<?php

namespace App\Entity;

use App\Entity\Booking\Booking;
use App\Entity\Rental\Rental;
use App\Entity\Subscription\Discount;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity('email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, \Serializable, \Stringable
{
    use IdentityTrait, TimestampabbleTrait;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\Email]
    private string $email;

    /** @var array<string> */
    #[ORM\Column(type: 'json')]
    private array $roles = ['ROLE_USER'];

    #[ORM\Column(type: 'string')]
    private string $password;

    /** @var ArrayCollection<int, Rental> */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Rental::class)]
    private Collection $rentals;

    #[ORM\OneToOne(inversedBy: 'account', targetEntity: Profile::class, cascade: ['persist', 'remove'])]
    private ?Profile $profile = null;

    /** @var ArrayCollection<int, Discount> */
    #[ORM\OneToMany(mappedBy: 'payee', targetEntity: Discount::class)]
    private Collection $discounts;

    /** @var ArrayCollection<int, Booking> */
    #[ORM\OneToMany(mappedBy: 'booker', targetEntity: Booking::class)]
    private Collection $bookings;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $resetToken;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $resetAt;

    public function __construct()
    {
        $this->rentals = new ArrayCollection();
        $this->discounts = new ArrayCollection();

        $this->createdAt = new \DateTime('now');
        $this->updatedAt = new \DateTime('now');
        $this->bookings = new ArrayCollection();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function __toString(): string
    {
        return $this->email;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     * @psalm-return array<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
    }

    /** @psalm-return ArrayCollection<int, Rental>  */
    public function getRentals(): Collection
    {
        return $this->rentals;
    }

    public function addRental(Rental $rental): self
    {
        if (!$this->rentals->contains($rental)) {
            $this->rentals[] = $rental;
            $rental->setOwner($this);
        }

        return $this;
    }

    public function removeRental(Rental $rental): self
    {
        if ($this->rentals->removeElement($rental)) {
            // set the owning side to null (unless already changed)
            if ($rental->getOwner() === $this) {
                $rental->setOwner(null);
            }
        }

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): self
    {
        $this->profile = $profile;
        $profile?->setAccount($this);

        return $this;
    }

    /** @psalm-return ArrayCollection<int, Discount>  */
    public function getDiscounts(): Collection
    {
        return $this->discounts;
    }

    public function addDiscount(Discount $discount): self
    {
        if (!$this->discounts->contains($discount)) {
            $this->discounts[] = $discount;
            $discount->setPayee($this);
        }

        return $this;
    }

    public function removeDiscount(Discount $discount): self
    {
        if ($this->discounts->removeElement($discount)) {
            // set the owning side to null (unless already changed)
            if ($discount->getPayee() === $this) {
                $discount->setPayee(null);
            }
        }

        return $this;
    }

    public function serialize(): ?string
    {
        return serialize([
            $this->id,
            $this->email,
            $this->password,
            $this->roles,
        ]);
    }

    public function unserialize(string $data)
    {
        /** @phpstan-ignore-next-line */
        [
            /** @phpstan-ignore-next-line */
            $this->id,
            /** @phpstan-ignore-next-line */
            $this->email,
            /** @phpstan-ignore-next-line */
            $this->password,
            /** @phpstan-ignore-next-line */
            $this->roles,
        ] = unserialize($data);
    }

    /** @return ArrayCollection<int, Booking>  */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings[] = $booking;
            $booking->setBooker($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        $this->bookings->removeElement($booking);

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(string $resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    public function getResetAt(): ?\DateTime
    {
        return $this->resetAt;
    }

    public function setResetAt(\DateTime $resetAt): self
    {
        $this->resetAt = $resetAt;
        return $this;
    }

    public function requestResetPassword(string $token): void
    {
        $this->resetToken = $token;
        $this->resetAt = new \DateTime();
    }

    public function resetPassword(string $hashPassword): void
    {
        $this->password = $hashPassword;
        $this->resetToken = null;
        $this->resetAt = null;
    }
}
