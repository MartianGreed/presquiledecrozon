<?php

namespace App\MessageHandler;

use App\Domain\Exception\RentalNotFoundException;
use App\Domain\Notifications;
use App\Entity\Notification;
use App\Message\RentalHasBeenPublished;
use App\MessageHandler\Traits\RentalFetcherTrait;
use App\Repository\Rental\RentalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;

#[AsMessageHandler]
final class RentalHasBeenPublishedHandler
{
    use RentalFetcherTrait;

    public function __construct(
        private readonly RentalRepository $rentalRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly MailerInterface $mailer,
        private readonly string $emailSender,
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

        // Email owner to confirm rental has been published
        $email = (new TemplatedEmail())
            ->from($this->emailSender)
            ->to(new Address((string)$rental->getOwner()?->getEmail()))
            ->subject('Votre annonce à bien été publiée !')
            ->htmlTemplate('emails/rental_has_been_published.html.twig')
            ->context([
                'rental' => $rental,
            ])
        ;
        $this->mailer->send($email);

        $notification = Notification::create($rental, Notifications::RENTAL_HAS_BEEN_PUBLISHED->value, new \DateTime());

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}
