<?php

namespace App\Message;

final class FetchRentalGeolocation
{
    private string $rentalId;
    private string $address;
    private ?string $address2;
    private string $town;
    private string $postalCode;

    public function __construct(string $rentalId, string $address, string $town, string $postalCode, ?string $address2 = null)
    {
        $this->rentalId = $rentalId;
        $this->address = $address;
        $this->address2 = $address2;
        $this->town = $town;
        $this->postalCode = $postalCode;
    }

    public function getRentalId(): string
    {
        return $this->rentalId;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getAddress2(): string
    {
        return $this->address2;
    }

    public function getTown(): string
    {
        return $this->town;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }
}
