<?php

namespace App\MessageHandler;

use App\Domain\Exception\RentalNotFoundException;
use App\Message\RentalHasBeenPublished;
use App\MessageHandler\Traits\RentalFetcherTrait;
use App\Repository\Rental\RentalRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class RentalHasBeenPublishedHandler implements MessageHandlerInterface
{
    use RentalFetcherTrait;

    public function __construct(
        private readonly RentalRepository $rentalRepository,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function __invoke(RentalHasBeenPublished $message): void
    {
        try {
            $rental = $this->withRental($this->rentalRepository, $this->logger, $message->rentalId);
        } catch (RentalNotFoundException) {
            // Do nothing here, log is done in the upper method, just return silently.
            return;
        }

        // Send an email to confirm owner rental has been published
    }
}
