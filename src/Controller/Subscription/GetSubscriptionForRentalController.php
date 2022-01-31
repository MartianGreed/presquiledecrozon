<?php

namespace App\Controller\Subscription;

use App\Domain\Exception\DefaultSubscriptionNotFound;
use App\Domain\Exception\RentalNotFoundException;
use App\Domain\Rental\Service\RentalService;
use App\Domain\Subscription\Repository\SubscriptionRepository;
use App\Form\CreateSubscriptionType;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GetSubscriptionForRentalController extends AbstractController
{
    public function __construct(
        private readonly RentalService $rentalService,
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly StripeService $stripeService,
    ) {}

    #[Route('/abonnement', name: 'app_get_subscription')]
    public function __invoke(Request $request): Response
    {
        $rentalId = $request->query->get('rental_id');
        if (null === $rentalId) {
            return $this->redirectToRoute('app_homepage');
        }
        try {
            $rental = $this->rentalService->findRental((string) $rentalId);

            try {
                $subscription = $this->subscriptionRepository->findDefaultSubscription();
            } catch (DefaultSubscriptionNotFound $e) {
                return $this->render('subscription/default-not-found.html.twig');
            }

            $form = $this->createForm(CreateSubscriptionType::class);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                dd($form->getData());
            }

            return $this->renderForm('subscription/create.html.twig', [
                'rental' => $rental,
                'subscription' => $subscription,
                'expirationDate' => $subscription->getSubscriptionExpirationDate(new \DateTime('now')),
                'form' => $form,
                'payment_intent' => $this->stripeService->createSubscriptionPaymentIntent($subscription),
            ]);
        } catch (RentalNotFoundException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }
    }
}