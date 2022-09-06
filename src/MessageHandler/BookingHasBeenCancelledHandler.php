<?php

namespace App\MessageHandler;

use App\Domain\Notifications;
use App\Entity\Booking\Booking;
use App\Entity\Notification;
use App\Message\BookingHasBeenCancelled;
use App\Repository\Booking\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mime\Address;

final class BookingHasBeenCancelledHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly BookingRepository $bookingRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly string $emailSender,
    )
    {
    }

    public function __invoke(BookingHasBeenCancelled $message): void
    {
        $booking = $this->bookingRepository->find($message->bookingId);
        if (null === $booking) {
            $this->logger->error('Booking not found with id : ' . $message->bookingId);
            return;
        }

        $this->confirmBookerBookingsHasBeenCancelled($booking);

        $notification = Notification::create($booking, Notifications::BOOKING_HAS_BEEN_CANCELLED->value, new \DateTime());

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    private function confirmBookerBookingsHasBeenCancelled(Booking $booking): void
    {
        $email = (new TemplatedEmail())
            ->from($this->emailSender)
            ->to(new Address((string)$booking->getBooker()->getEmail()))
            ->subject('Votre réservation vient d\'être annulée...')
            ->htmlTemplate('emails/booker_booking_has_been_cancelled.html.twig')
        ;

        $this->mailer->send($email);
    }
}
