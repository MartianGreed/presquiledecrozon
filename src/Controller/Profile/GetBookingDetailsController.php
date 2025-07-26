<?php

declare(strict_types=1);

namespace App\Controller\Profile;

use App\Controller\WithUserTrait;
use App\Domain\Booking\ViewModel\Confirmation;
use App\Repository\Booking\BookingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GetBookingDetailsController extends AbstractController
{
    use WithUserTrait;

    public function __construct(private readonly BookingRepository $bookingRepository)
    {
    }

    #[Route('/mon-compte/reservation/{id}', name: 'app_profile_booking_details')]
    public function __invoke(Request $request, string $id): Response
    {
        $booking = $this->bookingRepository->find($id);
        if (null === $booking) {
            throw $this->createNotFoundException('Booking not found');
        }

        if ($this->getUser()->getId() !== $booking->getRental()->getOwner()?->getId()) {
            throw $this->createAccessDeniedException('You cannot access this resource');
        }

        return $this->render('profile/get_booking_details.html.twig', [
            'vm' => new Confirmation($booking),
        ]);
    }
}
