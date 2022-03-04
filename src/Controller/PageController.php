<?php

namespace App\Controller;

use App\Domain\Exception\DefaultSubscriptionNotFound;
use App\Repository\Rental\RentalRepository;
use App\Repository\Subscription\SubscriptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(
        RentalRepository $rentalRepository,
        SubscriptionRepository $subscriptionRepository
    ): Response
    {
        $subscription = null;
        try {
            $subscription = $subscriptionRepository->findDefaultSubscription();
        } catch (DefaultSubscriptionNotFound) {
            // Do nothing since this case is not really going to happen for now.
        }

        return $this->render('page/index.html.twig', [
            'rentals' => $rentalRepository->findFeatured(),
            'subscription' => $subscription,
        ]);
    }
}
