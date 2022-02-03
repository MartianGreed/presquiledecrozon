<?php

namespace App\Entity\Rental;

use App\Domain\Price as PriceVO;
use App\Domain\Rental\DTO\GeolocationDTO;
use App\Domain\Rental\Status;
use App\Entity\Data\Furniture;
use App\Entity\IdentityTrait;
use App\Entity\Rental\Traits\RentalAccessorTrait;
use App\Entity\Subscription\RentalSubscription;
use App\Entity\TimestampabbleTrait;
use App\Entity\User;
use App\Repository\Rental\RentalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RentalRepository::class)]
#[ORM\Index(columns: ['slug'], name: 'slug_idx')]
class Rental
{
    use IdentityTrait, TimestampabbleTrait, RentalAccessorTrait;

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

    public function __construct()
    {
        $this->furnitures = new ArrayCollection();
        $this->unavailabilities = new ArrayCollection();
        $this->prices = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
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
            ->setTitle((string)$description->getTitle())
            ->setDescription((string)$description->getDescription())
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
            ->setAddress((string)$address->getAddress())
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
}
