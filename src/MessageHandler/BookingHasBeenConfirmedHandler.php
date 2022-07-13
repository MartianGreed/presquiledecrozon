<?php

namespace App\MessageHandler;

use App\Message\BookingHasBeenConfirmed;
use App\Repository\Booking\BookingRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class BookingHasBeenConfirmedHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly BookingRepository $bookingRepository,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function __invoke(BookingHasBeenConfirmed $message)
    {
        $booking = $this->bookingRepository->find($message->bookingId);
        if (null === $booking) {
            $this->logger->error('Booking not found with id : ' . $message->bookingId);
            return;
        }

        // Send an email to the booker to notify him his booking has been confirmed
        // Send an email to the owner to confirm his decision has properly been taken in account.
    }
}
