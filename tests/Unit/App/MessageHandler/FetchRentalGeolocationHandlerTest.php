<?php

namespace App\Tests\Unit\App\MessageHandler;

use App\Domain\Rental\DTO\GeolocationDTO;
use App\Domain\Rental\Service\GeocodingServiceInterface;
use App\Entity\Rental\Geolocation;
use App\Entity\Rental\Rental;
use App\Message\FetchRentalGeolocation;
use App\MessageHandler\FetchRentalGeolocationHandler;
use App\Repository\Rental\RentalRepository;
use App\Tests\Unit\App\Factory\Rental\AddressFactory;
use App\Tests\Unit\App\Factory\TownFactory;
use App\Tests\Unit\App\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FetchRentalGeolocationHandlerTest extends TestCase
{
    private MockObject $repository;
    private MockObject $geocodingService;
    private MockObject $manager;

    private FetchRentalGeolocationHandler $handler;

    public function setUp(): void
    {
        $this->repository = $this->createMock(RentalRepository::class);
        $this->geocodingService = $this->createMock(GeocodingServiceInterface::class);
        $this->manager = $this->createMock(EntityManagerInterface::class);

        $this->handler = new FetchRentalGeolocationHandler(
            $this->repository,
            $this->geocodingService,
            $this->manager,
        );
    }

    public function testItThrowsAnExceptionIfRentalIsNull(): void
    {
        $this->repository->expects($this->once())->method('find')->willReturn(null)->with('AFakeIdGivingNull');

        $this->expectException(\LogicException::class);

        call_user_func($this->handler, new FetchRentalGeolocation('AFakeIdGivingNull'));
    }

    public function testItThrowsAnExceptionIfAddressIsNull(): void
    {
        $this->repository->expects($this->once())->method('find')->willReturn(self::getRental())->with('AValidId');

        $this->expectException(\LogicException::class);

        call_user_func($this->handler, new FetchRentalGeolocation('AValidId'));
    }

    public function testItFetchGeolocationForAddress(): void
    {
        $rental = self::getRental(true);
        $this->repository->expects($this->once())->method('find')->willReturn($rental)->with('AValidId');

        $this->geocodingService->expects($this->once())->method('geocode')->with($rental->getAddress())->willReturn(GeolocationDTO::new(15, 15));

        $this->manager->expects($this->once())->method('persist')->with(Geolocation::new(['lat' => 15, 'lng' => 15, 'meta' => []]));
        $this->manager->expects($this->once())->method('flush');

        call_user_func($this->handler, new FetchRentalGeolocation('AValidId'));
    }

    private static function getRental(bool $withAddress = false): Rental
    {
        $rental = Rental::new(UserFactory::createUser());

        if ($withAddress) {
            $address = AddressFactory::createAddress('Lezargol', TownFactory::argol(), $rental);
        }

        return $rental;
    }
}
