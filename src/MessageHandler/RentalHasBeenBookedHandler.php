<?php

namespace App\MessageHandler;

use App\Domain\Exception\RentalNotFoundException;
use App\Message\RentalHasBeenBooked;
use App\MessageHandler\Traits\RentalFetcherTrait;
use App\Repository\Rental\RentalRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class RentalHasBeenBookedHandler implements MessageHandlerInterface
{
    use RentalFetcherTrait;

    public function __construct(
        private readonly RentalRepository $rentalRepository,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function __invoke(RentalHasBeenBooked $message): void
    {
        try {
            $rental = $this->withRental($this->rentalRepository, $this->logger, $message->rentalId);
        } catch (RentalNotFoundException) {
            // Do nothing here, log is done in the upper method, just return silently.
            return;
        }

        // Send an email to notify owner rental has been booked.
        // Send an email to confirm the booker his booking has been taken in account.
    }
}
