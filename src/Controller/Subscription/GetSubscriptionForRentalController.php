<?php

namespace App\Controller\Subscription;

use App\Domain\Exception\RentalNotFoundException;
use App\Domain\Rental\Service\RentalService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GetSubscriptionForRentalController extends AbstractController
{
    public function __construct(private readonly RentalService $rentalService)
    {
    }

    #[Route('/abonnement', name: 'app_get_subscription')]
    public function __invoke(Request $request): Response
    {
        $rentalId = $request->query->get('rental_id');
        if (null === $rentalId) {
            return $this->redirectToRoute('app_homepage');
        }
        try {
            $rental = $this->rentalService->findRental((string) $rentalId);
            return $this->render('subscription/create.html.twig', [
                'rental' => $rental,
            ]);
        } catch (RentalNotFoundException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }
    }
}