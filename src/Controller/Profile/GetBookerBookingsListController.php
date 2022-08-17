<?php

namespace App\Controller\Profile;

use App\Controller\WithUserTrait;
use App\Repository\Booking\BookingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GetBookerBookingsListController extends AbstractController
{
    use WithUserTrait;

    public function __construct(private readonly BookingRepository $bookingRepository)
    {
    }

    #[Route('/mon-profil/vacances', name: 'app_profile_booker_bookings_list')]
    public function __invoke(Request $request): Response
    {
        $userId = (string) $this->getUser()->getId();

        return $this->render('profile/get_bookings_listing.html.twig', [
            'is_owner' => false,
            'past_bookings' => $this->bookingRepository->getUserPastBookings($userId),
            'forthcoming_bookings' => $this->bookingRepository->getUserForthcomingBookings($userId),
            'title' => 'Vacances',
        ]);
    }
}
