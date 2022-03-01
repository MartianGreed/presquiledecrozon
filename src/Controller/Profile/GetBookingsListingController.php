<?php

namespace App\Controller\Profile;

use App\Controller\WithUserTrait;
use App\Repository\Booking\BookingRepository;
use App\Repository\Rental\RentalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GetBookingsListingController extends AbstractController
{
    use WithUserTrait;

    public function __construct(
        private readonly BookingRepository $bookingRepository,
        private readonly RentalRepository $rentalRepository,
    )
    {
    }

    #[Route('/mon-compte/reservations', name: 'app_profile_booking')]
    public function __invoke(Request $request): Response
    {
        $userId = (string) $this->getUser()->getId();
        $isOwner = $this->rentalRepository->userHasRental($userId);

        $pastBookings = $isOwner
            ? $this->bookingRepository->getOwnerBookingsHistory($userId)
            : $this->bookingRepository->getUserPastBookings($userId)
        ;
        $forthComingBookings = $isOwner
            ? $this->bookingRepository->getOwnerForthcomingBookings($userId)
            : $this->bookingRepository->getUserForthcomingBookings($userId)
        ;

        return $this->render('profile/get_bookings_listing.html.twig', [
            'is_owner' => $isOwner,
            'past_bookings' => $pastBookings,
            'forthcoming_bookings' => $forthComingBookings,
            'to_confirm_bookings' => $this->bookingRepository->getOwnerBookingsToValidate($userId),
        ]);
    }
}