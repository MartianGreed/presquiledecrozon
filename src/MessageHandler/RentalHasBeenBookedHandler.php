<?php

namespace App\MessageHandler;

use App\Domain\Exception\RentalNotFoundException;
use App\Domain\Notifications;
use App\Entity\Booking\Booking;
use App\Entity\Notification;
use App\Entity\Rental\Rental;
use App\Message\RentalHasBeenBooked;
use App\MessageHandler\Traits\RentalFetcherTrait;
use App\Repository\Booking\BookingRepository;
use App\Repository\Rental\RentalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;

#[AsMessageHandler]
final class RentalHasBeenBookedHandler
{
    use RentalFetcherTrait;

    public function __construct(
        private readonly RentalRepository $rentalRepository,
        private readonly BookingRepository $bookingRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly string $emailSender,
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
        $booking = $this->bookingRepository->find($message->bookingId);

        if (null === $booking) {
            $this->logger->error('Booking not found with id : ' . $message->bookingId);
            return;
        }

        $this->notifyOwnerRentalHasBeenBooked($rental);
        $this->confirmBookerBookingsHasBeenTakenInAccount($booking);

        $notification = Notification::create($rental, Notifications::RENTAL_HAS_BEEN_BOOKED->value, new \DateTime());

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    private function notifyOwnerRentalHasBeenBooked(Rental $rental): void
    {
        $email = (new TemplatedEmail())
            ->from($this->emailSender)
            ->to(new Address((string)$rental->getOwner()?->getEmail()))
            ->subject('Nouvelle demande de réservation')
            ->htmlTemplate('emails/owner_rental_has_been_booked.html.twig')
        ;

        $this->mailer->send($email);
    }

    private function confirmBookerBookingsHasBeenTakenInAccount(Booking $booking): void
    {
        $email = (new TemplatedEmail())
            ->from($this->emailSender)
            ->to(new Address((string)$booking->getBooker()->getEmail()))
            ->subject('Votre réservation a bien été prise en compte')
            ->htmlTemplate('emails/booker_rental_has_been_booked.html.twig')
        ;

        $this->mailer->send($email);
    }
}
