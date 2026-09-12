<?php

namespace App\Service;

use App\Domain\Rental\DTO\GeolocationDTO;
use App\Domain\Rental\Service\GeocodingServiceInterface;
use App\Entity\Data\Town;
use App\Entity\Rental\Address;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GeocodingService implements GeocodingServiceInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $apiUrl,
        private readonly string $apiKey
    )
    {
    }

    public function geocode(Address $address): GeolocationDTO
    {
        $res = $this->client->request(
            Request::METHOD_GET,
            $this->buildUri($this->apiUrl, $this->formatAddress($address), $this->apiKey)
        );

        if (200 !== $res->getStatusCode()) {
            throw new \LogicException('Failed to communicate with provider. Please retry later');
        }

        $data = $res->toArray()['results'];
        $result = array_shift($data);

        $geometry = $result['geometry'];

        return GeolocationDTO::new(
            $geometry['location']['lat'],
            $geometry['location']['lng'],
            [
                'viewport' => $geometry['viewport'],
                'formatted_address' => $result['formatted_address'],
                'place_id' => $result['place_id'],
            ]
        );
    }

    private function buildUri(string $uri, string $address, string $apiKey): string
    {
        return sprintf('%s?address=%s&key=%s', $uri, $address, $apiKey);
    }

    private function formatAddress(Address $address): string
    {
        $formatted = $address->getAddress();
        if (null !== $address->getAddress2()) {
            $formatted .= ',+' . $address->getAddress2();
        }

        $town = $this->getTownObjectFromAddress($address);

        $formatted .= ',+' . $town->getName();
        $formatted .= ',+' . $town->getPostalCode()->getCode();

        return $formatted . (',+' . $town->getPostalCode()->getDepartment()->getRegion()->getCountry()->getName());
    }

    private function getTownObjectFromAddress(Address $address): Town
    {
        $town = $address->getTown();
        if (! $town instanceof \App\Entity\Data\Town) {
            throw new \LogicException('Town cannot be null to geocode an Address');
        }

        return $town;
    }
}
