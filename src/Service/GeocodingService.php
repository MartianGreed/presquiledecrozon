<?php

namespace App\Service;

use App\Domain\Rental\DTO\GeolocationDTO;
use App\Entity\Rental\Address;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GeocodingService
{
    private HttpClientInterface $client;
    private string $apiUrl;
    private string $apiKey;

    public function __construct(HttpClientInterface $client, string $apiUrl, string $apiKey)
    {
        $this->client = $client;
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
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
        $formatted .= ',+' . $address->getTown()->getName();
        $formatted .= ',+' . $address->getTown()->getPostalCode()->getCode();
        $formatted .= ',+' . $address->getTown()->getPostalCode()->getDepartment()->getRegion()->getCountry()->getName();

        return $formatted;
    }
}