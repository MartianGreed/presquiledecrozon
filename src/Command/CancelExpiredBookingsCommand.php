<?php

namespace App\Command;

use App\Repository\Booking\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:bookings:cancel-expired')]
final class CancelExpiredBookingsCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly BookingRepository $bookingRepository,
        private readonly EntityManagerInterface $manager,
    ) {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->title('Cancel expired bookings');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $expiringBookings = $this->bookingRepository->getExpiringConfirmedBookings();

        if (0 === \count($expiringBookings)) {
            $this->io->success('No expiring bookings');
        }

        $cancelledAt = new \DateTime();
        foreach ($expiringBookings as $booking) {
            $booking->cancel($cancelledAt);
        }

        $this->manager->flush();

        return self::SUCCESS;
    }
}
