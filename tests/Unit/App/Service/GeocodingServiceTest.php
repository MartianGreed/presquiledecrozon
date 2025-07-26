<?php

namespace App\Tests\Unit\App\Service;

use App\Entity\Rental\Address;
use App\Service\GeocodingService;
use App\Tests\Unit\App\Factory\TownFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class GeocodingServiceTest extends TestCase
{
    private MockObject $clientMock;
    private GeocodingService $service;

    public function setUp(): void
    {
        $this->clientMock = $this->createMock(HttpClientInterface::class);

        $this->service = new GeocodingService($this->clientMock, 'https://geocodingapi.com', 'anApiKey');
    }

    public function testItProperlyCallsAnApi(): void
    {
        $address = $this->createAddress();

        $this->clientMock
            ->expects($this->once())->method('request')
                                    ->with(Request::METHOD_GET, 'https://geocodingapi.com?address=Lezargol,+Argol,+29560,+France&key=anApiKey')
            ->willReturn($this->createResponseInterfaceMock())
        ;

        $res = $this->service->geocode($address);

        self::assertArrayHasKey('lat', $res->toArray());
        self::assertArrayHasKey('lng', $res->toArray());
        self::assertArrayHasKey('meta', $res->toArray());
    }

    private function createAddress(): Address
    {
        $town = TownFactory::argol();

        $addressStr = 'Lezargol';
        $address2 = null;

        $address = new Address();
        $address->setAddress($addressStr)->setAddress2($address2)->setTown($town);

        return $address;
    }

    private function createResponseInterfaceMock(): ResponseInterface
    {
        /** @var string $responseContent */
        $responseContent = file_get_contents(dirname(__DIR__, 3).'/samples/google/lezargol.json');

        $responseMock = $this->createMock(ResponseInterface::class);

        $responseMock->method('getStatusCode')->willReturn(200);
        $responseMock->method('getContent')->willReturn($responseContent);
        $responseMock->method('toArray')->willReturn(json_decode($responseContent, true, 512, JSON_THROW_ON_ERROR));

        return $responseMock;
    }
}
