<?php

namespace App\Entity\Rental;

use App\Domain\Booking\BookingPrices;
use App\Domain\Exception\NoSubscriptionsFoundForRentalException;
use App\Domain\Price as PriceVO;
use App\Domain\Rental\DTO\GeolocationDTO;
use App\Domain\Rental\Status;
use App\Domain\Subscription\SubscriptionStatus;
use App\Entity\Booking\Booking;
use App\Entity\Data\Furniture;
use App\Entity\Identity;
use App\Entity\IdentityTrait;
use App\Entity\Rental\Traits\RentalAccessorTrait;
use App\Entity\Subscription\RentalSubscription;
use App\Entity\TimestampabbleTrait;
use App\Entity\User;
use App\Repository\Rental\RentalRepository;
use App\Util\Type;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RentalRepository::class)]
#[ORM\Index(columns: ['slug'], name: 'slug_idx')]
class Rental implements Identity
{
    use IdentityTrait;
    use TimestampabbleTrait;
    use RentalAccessorTrait;

    #[ORM\Column(type: 'rental_status')]
    private Status $status = Status::DRAFT;

    #[ORM\OneToOne(mappedBy: 'rental', targetEntity: Configuration::class, cascade: ['persist', 'remove'], fetch: 'EAGER')]
    private ?Configuration $configuration = null;

    /** @var ArrayCollection<int, Furniture> */
    #[ORM\ManyToMany(targetEntity: Furniture::class)]
    private Collection $furnitures;

    /** @var array<int, string> */
    #[ORM\Column(type: 'array')]
    private array $customFurnitures = [];

    #[ORM\OneToOne(inversedBy: 'rental', targetEntity: Description::class, cascade: ['persist', 'remove'])]
    private ?Description $description = null;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    private ?string $slug = null;

    #[ORM\OneToOne(inversedBy: 'rental', targetEntity: Address::class, cascade: ['persist', 'remove'])]
    private ?Address $address = null;

    #[ORM\OneToOne(inversedBy: 'rental', targetEntity: Geolocation::class, cascade: ['persist', 'remove'])]
    private ?Geolocation $geolocation = null;

    #[ORM\OneToOne(inversedBy: 'rental', targetEntity: Gallery::class, cascade: ['persist', 'remove'])]
    private ?Gallery $gallery = null;

    #[ORM\OneToOne(inversedBy: 'rental', targetEntity: Preferences::class, cascade: ['persist', 'remove'], fetch: 'EAGER')]
    private ?Preferences $preferences = null;

    /** @var ArrayCollection<int, Unavailability> */
    #[ORM\OneToMany(mappedBy: 'rental', targetEntity: Unavailability::class, orphanRemoval: true, cascade: [
        'persist',
        'remove',
    ], fetch: 'EAGER')]
    private Collection $unavailabilities;

    #[ORM\OneToOne(inversedBy: 'rental', targetEntity: Tax::class, cascade: ['persist', 'remove'])]
    private ?Tax $tax = null;

    #[ORM\OneToOne(inversedBy: 'rental', targetEntity: Condition::class, cascade: ['persist', 'remove'])]
    private ?Condition $condition = null;

    /** @var ArrayCollection<int, Price> */
    #[ORM\OneToMany(mappedBy: 'rental', targetEntity: Price::class, orphanRemoval: true, cascade: [
        'persist',
        'remove',
    ])]
    private Collection $prices;

    #[ORM\Column(type: 'price', nullable: true)]
    private ?PriceVO $weeklyRate = null;

    #[ORM\Column(type: 'price', nullable: true)]
    private ?PriceVO $dailyRate = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'rentals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    /** @var ArrayCollection<int, RentalSubscription> */
    #[ORM\OneToMany(mappedBy: 'rental', targetEntity: RentalSubscription::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Collection $subscriptions;

    #[ORM\OneToOne(mappedBy: 'activeRental', targetEntity: RentalSubscription::class)]
    private ?RentalSubscription $activeSubscription = null;

    /** @var ArrayCollection<int, Booking> */
    #[ORM\OneToMany(mappedBy: 'rental', targetEntity: Booking::class)]
    private Collection $bookings;

    public function __construct()
    {
        $this->furnitures = new ArrayCollection();
        $this->unavailabilities = new ArrayCollection();
        $this->prices = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
        $this->bookings = new ArrayCollection();
    }

    public static function new(User $owner): Rental
    {
        return (new self())
            ->setStatus(Status::DRAFT)
            ->setOwner($owner)
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
        ;
    }

    final public function saveDescription(Description $description): self
    {
        if (null === $this->description) {
            $this->setDescription($description);

            return $this;
        }

        $this->description
            ->setTitle((string) $description->getTitle())
            ->setDescription((string) $description->getDescription())
        ;

        return $this;
    }

    final public function saveAddress(Address $address): self
    {
        if (null === $this->address) {
            $this->setAddress($address);

            return $this;
        }

        $this->address
            ->setAddress((string) $address->getAddress())
            ->setAddress2($address->getAddress2())
            ->setTown($address->getTown())
        ;

        return $this;
    }

    final public function improveGeolocation(GeolocationDTO $geolocationDTO): self
    {
        $this->geolocation = Geolocation::new($geolocationDTO->toArray());

        return $this;
    }

    final public function createGallery(Gallery $gallery): self
    {
        if ($this->gallery !== $gallery) {
            $this->setGallery($gallery);
        }

        return $this;
    }

    /** @param array<Unavailability> $unavailabilities */
    final public function saveUnavailabilities(array $unavailabilities): self
    {
        foreach ($unavailabilities as $unavailability) {
            $unavailability->setRental($this);
        }

        return $this;
    }

    final public function saveTax(Tax $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    /** @param ArrayCollection<int, Price> $prices */
    final public function savePrices(Collection $prices): self
    {
        /** @var Price $price */
        foreach ($prices as $price) {
            $price->setRental($this);
        }

        return $this;
    }

    final public function isPublished(): bool
    {
        return Status::PUBLISHED === $this->status;
    }

    final public function isPublishable(): bool
    {
        $paidSubscriptions = $this->getPaidSubscriptions();

        return (!$this->isPublished() && 0 < count($paidSubscriptions)) || (!$this->isPublished() && null !== $this->activeSubscription);
    }

    final public function hasActiveSubscription(): bool
    {
        return null !== $this->activeSubscription && $this->activeSubscription->isStillRunning();
    }

    final public function publish(): self
    {
        $publishedAt = new \DateTime('now');

        if (null === $this->activeSubscription) {
            $paidSubscriptions = $this->getPaidSubscriptions();
            if (0 === count($paidSubscriptions)) {
                throw new NoSubscriptionsFoundForRentalException((string) $this->id);
            }

            $firstPaidSubscription = $paidSubscriptions->first();
            assert($firstPaidSubscription instanceof RentalSubscription);

            $this->activeSubscription = $firstPaidSubscription;
            $this->activeSubscription->setExpiresAt($this->activeSubscription->getSubscription()->getSubscriptionExpirationDate(clone $publishedAt));
            $this->activeSubscription->setIsConsumed(true);
            $this->activeSubscription->setUpdatedAt(clone $publishedAt);
            $this->activeSubscription->setActiveRental($this);
        }

        if ($publishedAt < $this->activeSubscription->getExpiresAt()) {
            $this->status = Status::PUBLISHED;
            $this->updatedAt = $publishedAt;
        }

        return $this;
    }

    final public function disable(): self
    {
        $this->status = Status::DISABLED;
        $this->updatedAt = new \DateTime('now');

        return $this;
    }

    /** @return ArrayCollection<int, RentalSubscription> */
    private function getPaidSubscriptions(): Collection
    {
        return $this->subscriptions->filter(fn (RentalSubscription $rs) => null !== $rs->getProviderChargeId() && null === $rs->getExpiresAt() && !$rs->isConsumed() && SubscriptionStatus::ACTIVE === $rs->getStatus());
    }

    /** @return array{0: PriceVO, 1: PriceVO} */
    private function getDayPrice(\DateTimeInterface $date): array
    {
        foreach ($this->prices as $priceRange) {
            if ($date >= $priceRange->getRangeStart() && $date <= $priceRange->getRangeEnd()) {
                return [Type::assertNotNull($priceRange->getWeeklyRate()), Type::assertNotNull($priceRange->getDailyRate())];
            }
        }

        return [Type::assertNotNull($this->weeklyRate), Type::assertNotNull($this->dailyRate)];
    }

    final public function getPricesForRange(\DateTimeInterface $startAt, \DateTimeInterface $endAt): BookingPrices
    {
        $bookingPrices = new BookingPrices();

        $dateReference = \DateTime::createFromInterface($startAt);
        $nextDateReference = \DateTime::createFromInterface($dateReference)->add(new \DateInterval('P1D'));
        $periodDiff = $startAt->diff($endAt)->d - 1;

        $currentPrices = null;
        $count = 1;

        for ($i = 0; $i <= $periodDiff; ++$i) {
            [$weeklyRate, $dailyRate] = $this->getDayPrice($dateReference);
            [$nextWeeklyRate, $nextDailyRate] = $this->getDayPrice($nextDateReference);

            if (null === $currentPrices) {
                $currentPrices = [$weeklyRate, $dailyRate];
            }

            $dateReference->add(new \DateInterval('P1D'));
            $nextDateReference->add(new \DateInterval('P1D'));

            [$currentWeeklyRate, $currentDailyRate] = $currentPrices;

            if (
                $currentWeeklyRate->equals($nextWeeklyRate)
                && $currentDailyRate->equals($nextDailyRate)
                && $i < $periodDiff
            ) {
                ++$count;
                continue;
            }

            $bookingPrices = $bookingPrices->addPrice($currentWeeklyRate, $currentDailyRate, $count);

            $count = 1;
            $currentPrices = null;
        }

        return $bookingPrices;
    }

    final public function tokenize(): string
    {
        $content = sprintf('%s_%s', $this->id, $this->slug);

        return sprintf(
            '%s-%s',
            md5($content),
            $content
        );
    }
}
