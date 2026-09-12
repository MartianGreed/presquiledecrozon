<?php

declare(strict_types=1);

namespace App\Tests\Unit\App\Domain\Booking\ViewModel;

use App\Domain\Booking\BookingPriceSimulatorService;
use App\Domain\Booking\BookingValidator;
use App\Domain\Booking\ViewModel\Confirmation;
use App\Entity\Rental\Rental;
use App\Tests\Unit\App\Factory\BookingFactory;
use App\Tests\Unit\App\Factory\RentalFactory;
use App\Tests\Unit\App\Factory\UserFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class ConfirmationTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<BookingValidator>
     */
    private ObjectProphecy $validator;

    private BookingPriceSimulatorService $simulatorService;

    public function setUp(): void
    {
        $this->validator = $this->configureValidator();
        $this->simulatorService = new BookingPriceSimulatorService();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getBookingDates')]
    public function testBookingDuration(string $expected, string $start, string $end): void
    {
        $owner = UserFactory::createUser();
        $booker = UserFactory::createUser(email: 'valentin.dosimont@gmail.com');

        $rental = RentalFactory::create($owner);
        $booking = BookingFactory::create($this->validator->reveal(), $rental, $booker, new \DateTime($start), new \DateTime($end), 3);
        $this->simulatorService->aggregatePrices($booking);
        $confirmation = new Confirmation($booking);

        self::assertEquals($expected, $confirmation->getBookingDuration());
    }

    public static function getBookingDates(): \Generator
    {
        yield ['2 jours', '2022-11-10', '2022-11-12'];
        yield ['1 jour', '2022-11-10', '2022-11-11'];
        yield ['1 semaine et 1 jour', '2022-11-10', '2022-11-18'];
        yield ['1 semaine', '2022-11-10', '2022-11-17'];
        yield ['2 semaines', '2022-11-10', '2022-11-24'];
        yield ['3 semaines', '2022-11-10', '2022-12-01'];
        yield ['3 semaines et 6 jours', '2022-11-10', '2022-12-07'];
    }

    /**
     * @return ObjectProphecy<BookingValidator>
     */
    private function configureValidator(): ObjectProphecy
    {
        $prophecy = $this->prophesize(BookingValidator::class);

        // @phpstan-ignore-next-line
        $prophecy->validateBooking(Argument::type(Rental::class), Argument::type(\DateTime::class), Argument::type(\DateTime::class), Argument::type('int'))->willReturn(true);

        return $prophecy;
    }
}
