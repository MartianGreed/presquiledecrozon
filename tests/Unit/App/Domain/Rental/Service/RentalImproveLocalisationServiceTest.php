<?php

namespace App\Tests\Unit\App\Domain\Rental\Service;

use App\Domain\Rental\DTO\GeolocationDTO;
use App\Domain\Rental\DTO\LocationSuggestion;
use App\Domain\Rental\Service\RentalImproveLocalisationService;
use App\Entity\Rental\Address;
use App\Entity\Rental\Geolocation;
use App\Entity\Rental\Rental;
use App\Repository\Data\PostalCodeRepository;
use App\Repository\Data\TownRepository;
use App\Tests\Unit\App\Factory\PostalCodeFactory;
use App\Tests\Unit\App\Factory\Rental\AddressFactory;
use App\Tests\Unit\App\Factory\TownFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RentalImproveLocalisationServiceTest extends TestCase
{
    private RentalImproveLocalisationService $service;
    private MockObject $townRepository;
    private MockObject $postalCodeRepository;

    public function setUp(): void
    {
        $this->townRepository = $this->createMock(TownRepository::class);
        $this->postalCodeRepository = $this->createMock(PostalCodeRepository::class);

        $this->service = new RentalImproveLocalisationService($this->townRepository, $this->postalCodeRepository);
    }

    public function testItProperlyThrowsWhenNoSuggestionsHaveBeenSet(): void
    {
        $rental = self::createRental();
        $geolocationDTO = self::createGeolocationDTO();
        $suggestedLocalisation = self::createSuggestedLocalisation(true);

        $this->expectExceptionMessage('No need to improve localisation if no suggestion has been chosen');
        $this->expectException(\DomainException::class);

        $this->service->improveLocalisation($rental, $geolocationDTO, $suggestedLocalisation);
    }

    public function testItThrowsExceptionIfLocationIsNotSupported(): void
    {
        $rental = self::createRental();
        $geolocationDTO = self::createGeolocationDTO();
        $suggestedLocalisation = self::createSuggestedLocalisation();

        $this->townRepository->expects($this->once())->method('findOneBy')->with(['name' => 'Argol'])->willReturn(null);
        $this->postalCodeRepository->expects($this->once())->method('findOneBy')->with(['code' => '29560'])->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Town or PostalCode is not supported');

        $this->service->improveLocalisation($rental, $geolocationDTO, $suggestedLocalisation);
    }

    public function testItUpdateTheGeolocationWithImprovedLocalisation(): void
    {
        $rental = self::createRental();
        $geolocationDTO = self::createGeolocationDTO();
        $suggestedLocalisation = self::createSuggestedLocalisation();

        /** @var Address $address */
        $address = $rental->getAddress();
        /** @var Geolocation $geolocation */
        $geolocation = $rental->getGeolocation();
        self::assertSame('3 place Marchais', $address->getAddress());
        self::assertSame(48.4444, $geolocation->getCoordinates()['lat']);
        self::assertSame(4.444, $geolocation->getCoordinates()['lng']);

        $this->townRepository->expects($this->once())->method('findOneBy')->with(['name' => 'Argol'])->willReturn(TownFactory::argol());
        $this->postalCodeRepository->expects($this->once())->method('findOneBy')->with(['code' => '29560'])->willReturn(PostalCodeFactory::create29560());

        $this->service->improveLocalisation($rental, $geolocationDTO, $suggestedLocalisation);

        /** @var Address $address */
        $address = $rental->getAddress();
        /** @var Geolocation $geolocation */
        $geolocation = $rental->getGeolocation();
        self::assertSame('Lézargol', $address->getAddress());
        self::assertSame(48.888, $geolocation->getCoordinates()['lat']);
        self::assertSame(4.888, $geolocation->getCoordinates()['lng']);
    }

    private static function createRental(): Rental
    {
        $rental = new Rental();

        AddressFactory::createAddress('3 place Marchais', TownFactory::argol(), $rental);

        $geolocation = Geolocation::new(
            [
                'lat' => 48.4444,
                'lng' => 4.444,
                'meta' => [
                    'viewport' => [
                        'northeast' => [
                            'lat' => 48.2207656,
                            'lng' => -4.2989526,
                        ],
                        'southwest' => [
                            'lat' => 48.2058951,
                            'lng' => -4.3309674,
                        ],
                    ],
                    'formatted_address' => 'Lézargol, 29560 Argol, France',
                    'place_id' => 'ChIJG5Dfm-zOFkgRLR1kI7XTyJk',
                ],
            ]
        );

        $rental->setGeolocation($geolocation);

        return $rental;
    }

    private static function createGeolocationDTO(): GeolocationDTO
    {
        return GeolocationDTO::new(48.888, 4.888, [
            'viewport' => [
                'northeast' => [
                    'lat' => 48.2207656,
                    'lng' => -4.2989526,
                ],
                'southwest' => [
                    'lat' => 48.2058951,
                    'lng' => -4.3309674,
                ],
            ],
            'formatted_address' => 'Lézargol, 29560 Argol, France',
            'place_id' => 'ChIJG5Dfm-zOFkgRLR1kI7XTyJk',
        ]);
    }

    private static function createSuggestedLocalisation(bool $isNull = false): LocationSuggestion
    {
        if ($isNull) {
            return new LocationSuggestion(null, null);
        }

        return new LocationSuggestion(
            'Lézargol, 29560 Argol, France',
            '{"address_components":[{"long_name":"Lézargol","short_name":"Lézargol","types":["route"]},{"long_name":"Argol","short_name":"Argol","types":["locality","political"]},{"long_name":"Finistère","short_name":"Finistère","types":["administrative_area_level_2","political"]},{"long_name":"Bretagne","short_name":"Bretagne","types":["administrative_area_level_1","political"]},{"long_name":"France","short_name":"FR","types":["country","political"]},{"long_name":"29560","short_name":"29560","types":["postal_code"]}],"formatted_address":"Lézargol, 29560 Argol, France","geometry":{"bounds":{"south":48.2132549,"west":-4.3152138,"north":48.21358499999999,"east":-4.3145218},"location":{"lat":48.2134246,"lng":-4.3148728},"location_type":"GEOMETRIC_CENTER","viewport":{"south":48.21207096970849,"west":-4.316216780291502,"north":48.21476893029149,"east":-4.313518819708498}},"place_id":"ChIJ-9XqYevOFkgRxMcR5OnRNW4","types":["route"]}',
        );
    }
}
