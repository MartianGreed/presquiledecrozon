<?php

namespace App\Controller\Rental;

use App\Controller\WithUserTrait;
use App\Domain\Rental\Service\RentalService;
use App\Entity\Rental\Rental;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RentalCreatedController extends AbstractController
{
    use WithUserTrait;

    public function __construct(
        private readonly RentalService $rentalService
    )
    {
    }

    #[Route('/deposez-votre-annonce/termine', name: 'app_rental_created')]
    public function __invoke(Request $request, ?Rental $rental): Response
    {
        if (! $rental instanceof \App\Entity\Rental\Rental) {
            $rental = $this->rentalService->findOrCreateRental($this->getUser());
        }

        if ($rental->isPublished() || $rental->hasActiveSubscription()) {
            return $this->redirectToRoute('app_profile_rental');
        }

        return $this->render('create_rental/finished.html.twig', [
            'rental' => $rental,
            'first_booking_at' => (new \DateTime())->add(new \DateInterval((string) $rental->getPreferences()?->getAcceptedLastBooking()))->format('d/m/Y'),
            'token' => $rental->tokenize(),
        ]);
    }
}
