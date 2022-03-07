<?php

namespace App\Controller\Profile;

use App\Controller\WithUserTrait;
use App\Domain\Booking\Exception\CannotManagerOtherOwnersRentalException;
use App\Message\BookingHasBeenCancelled;
use App\Repository\Booking\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

final class CancelBookingRequestController extends AbstractController
{
    use WithUserTrait;

    public function __construct(
        private readonly BookingRepository $bookingRepository,
        private readonly EntityManagerInterface $manager,
        private readonly MessageBusInterface $bus,
    ) {
    }

    #[Route('/mon-compte/reservation/{id}/annuler', name: 'app_profile_cancel_booking_request')]
    public function __invoke(Request $request, string $id): Response
    {
        $booking = $this->bookingRepository->find($id);
        if (null === $booking) {
            throw $this->createNotFoundException('Booking not found');
        }

        try {
            $cancelledAt = new \DateTime('now');
            $booking->cancelBooking($this->getUser(), $cancelledAt);

            $this->manager->flush();

            $this->bus->dispatch(new BookingHasBeenCancelled($booking->getId(), $cancelledAt->format('Y-m-d H:i:s')));
        } catch (CannotManagerOtherOwnersRentalException $e) {
            $this->addFlash('error', $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_profile_booking');
        }
    }
}
