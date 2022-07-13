<?php

namespace App\MessageHandler;

use App\Message\BookingHasBeenCancelled;
use App\Repository\Booking\BookingRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\Security;

final class BookingHasBeenCancelledHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly BookingRepository $bookingRepository,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function __invoke(BookingHasBeenCancelled $message)
    {
        $booking = $this->bookingRepository->find($message->bookingId);
        if (null === $booking) {
            $this->logger->error('Booking not found with id : ' . $message->bookingId);
            return;
        }

        // Send an email to the booker to notify him his booking has been cancelled
        // Send an email to the owner to confirm his decision has properly been taken in account.
    }
}
