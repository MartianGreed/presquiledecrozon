<?php

namespace App\Controller\Profile;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GetBookingsListingController extends AbstractController
{
    #[Route('/mon-compte/reservations', name: 'app_profile_booking')]
    public function __invoke(Request $request): Response
    {
        return $this->render('profile/get_bookings_listing.html.twig');
    }
}