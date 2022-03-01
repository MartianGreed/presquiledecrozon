<?php

namespace App\Domain\Rental\Service;

use App\Domain\Rental\DTO\GeolocationDTO;
use App\Domain\Rental\DTO\LocationSuggestion;
use App\Entity\Rental\Address;
use App\Entity\Rental\Rental;
use App\Repository\Data\PostalCodeRepository;
use App\Repository\Data\TownRepository;

/**
 * @phpstan-import-type AddressArray from AddressParser
 */
final class RentalImproveLocalisationService
{
    public function __construct(private readonly TownRepository $townRepository, private readonly PostalCodeRepository $postalCodeRepository)
    {
    }

    public function improveLocalisation(
        Rental $rental,
        GeolocationDTO $geolocationDTO,
        LocationSuggestion $suggestedLocalisation
    ): Rental {
        if ('' === $suggestedLocalisation->suggestions || null === $suggestedLocalisation->suggestions) {
            throw new \DomainException('No need to improve localisation if no suggestion has been chosen');
        }

        $suggestionAddressParts = AddressParser::getParts($suggestedLocalisation->suggestions);
        $suggestedAddress = $this->createAddressFromParts($suggestionAddressParts);

        $rental->saveAddress($suggestedAddress);
        $rental->improveGeolocation($geolocationDTO);

        return $rental;
    }

    /** @param AddressArray $suggestions */
    private function createAddressFromParts(array $suggestions): Address
    {
        $town = $this->townRepository->findOneBy(['name' => $suggestions['town']]);
        $postalCode = $this->postalCodeRepository->findOneBy(['code' => $suggestions['postal_code']]);
        if (null === $town || null === $postalCode) {
            throw new \DomainException('Town or PostalCode is not supported');
        }

        return (new Address())->setAddress($suggestions['address'])->setTown($town);
    }
}
