<?php

namespace App\Controller\Profile;

use App\Controller\WithUserTrait;
use App\Repository\Rental\RentalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GetRentalListingController extends AbstractController
{
    use WithUserTrait;

    public function __construct(private readonly RentalRepository $rentalRepository)
    {
    }

    #[Route('/mon-compte/annonces', name: 'app_profile_rental')]
    public function __invoke(Request $request): Response
    {
        $rentals = $this->rentalRepository->findUserRentals((string) $this->getUser()->getId());

        return $this->render('profile/get_rental_listing.html.twig', [
            'rentals' => $rentals,
        ]);
    }
}