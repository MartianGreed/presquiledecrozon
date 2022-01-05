<?php

namespace App\Entity\Rental\Traits;

use App\Domain\Rental\Status;
use App\Entity\Data\Furniture;
use App\Entity\Rental\Address;
use App\Entity\Rental\Condition;
use App\Entity\Rental\Configuration;
use App\Entity\Rental\Description;
use App\Entity\Rental\Geolocation;
use App\Entity\Rental\Preferences;
use App\Entity\Rental\Price;
use App\Entity\Rental\Tax;
use App\Entity\Rental\Unavailability;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait RentalAccessorTrait
{
    final public function getStatus(): Status
    {
        return $this->status;
    }

    final public function setStatus(Status $status): self
    {
        $this->status = $status;
        return $this;
    }

    final public function getConfiguration(): ?Configuration
    {
        return $this->configuration;
    }

    final public function setConfiguration(Configuration $configuration): self
    {
        // set the owning side of the relation if necessary
        if ($configuration->getRental() !== $this) {
            $configuration->setRental($this);
        }

        $this->configuration = $configuration;

        return $this;
    }

    /** @psalm-return ArrayCollection<int, Furniture>  */
    final public function getFurnitures(): Collection
    {
        return $this->furnitures;
    }

    final public function addFurniture(Furniture $furniture): self
    {
        if (!$this->furnitures->contains($furniture)) {
            $this->furnitures[] = $furniture;
        }

        return $this;
    }

    final public function removeFurniture(Furniture $furniture): self
    {
        $this->furnitures->removeElement($furniture);

        return $this;
    }

    /** @psalm-return array<int, string>  */
    final public function getCustomFurnitures(): ?array
    {
        return $this->customFurnitures;
    }

    /** @psalm-param array<int, string> $customFurnitures  */
    final public function setCustomFurnitures(array $customFurnitures): self
    {
        $this->customFurnitures = $customFurnitures;

        return $this;
    }

    final public function getDescription(): ?Description
    {
        return $this->description;
    }

    final public function setDescription(?Description $description): self
    {
        $this->description = $description;

        return $this;
    }

    final public function getSlug(): ?string
    {
        return $this->slug;
    }

    final public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    final public function getAddress(): ?Address
    {
        return $this->address;
    }

    final public function setAddress(?Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    final public function getGeolocation(): ?Geolocation
    {
        return $this->geolocation;
    }

    final public function setGeolocation(?Geolocation $geolocation): self
    {
        $this->geolocation = $geolocation;

        return $this;
    }

    final public function getPreferences(): ?Preferences
    {
        return $this->preferences;
    }

    final public function setPreferences(?Preferences $preferences): self
    {
        $this->preferences = $preferences;

        return $this;
    }

    /** @psalm-return ArrayCollection<int, Unavailability>  */
    final public function getUnavailabilities(): Collection
    {
        return $this->unavailabilities;
    }

    final public function addUnavailability(Unavailability $unavailability): self
    {
        if (!$this->unavailabilities->contains($unavailability)) {
            $this->unavailabilities[] = $unavailability;
            $unavailability->setRental($this);
        }

        return $this;
    }

    final public function removeUnavailability(Unavailability $unavailability): self
    {
        $this->unavailabilities->removeElement($unavailability);
        return $this;
    }

    final public function getTax(): ?Tax
    {
        return $this->tax;
    }

    final public function setTax(?Tax $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    final public function getCondition(): ?Condition
    {
        return $this->condition;
    }

    final public function setCondition(?Condition $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    /** @psalm-return ArrayCollection<int, Price>  */
    final public function getPrices(): Collection
    {
        return $this->prices;
    }

    final public function addPrice(Price $price): self
    {
        if (!$this->prices->contains($price)) {
            $this->prices[] = $price;
            $price->setRental($this);
        }

        return $this;
    }

    final public function removePrice(Price $price): self
    {
        $this->prices->removeElement($price);

        return $this;
    }

    final public function getWeeklyRate(): ?int
    {
        return $this->weeklyRate;
    }

    final public function setWeeklyRate(?int $weeklyRate): self
    {
        $this->weeklyRate = $weeklyRate;

        return $this;
    }

    final public function getDailyRate(): ?int
    {
        return $this->dailyRate;
    }

    final public function setDailyRate(int $dailyRate): self
    {
        $this->dailyRate = $dailyRate;

        return $this;
    }

    final public function getOwner(): ?User
    {
        return $this->owner;
    }

    final public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
