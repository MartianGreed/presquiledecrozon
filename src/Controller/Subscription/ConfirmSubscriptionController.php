<?php

namespace App\Controller\Subscription;

use App\Domain\Exception\RentalSubscriptionNotFound;
use App\Domain\Subscription\ConfirmationMessage;
use App\Entity\Rental\Rental;
use App\Repository\Subscription\RentalSubscriptionRepository;
use App\Service\StripeService;
use Stripe\Exception\ApiErrorException;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ConfirmSubscriptionController extends AbstractController
{
    public function __construct(
        private readonly StripeService $stripeService,
        private readonly RentalSubscriptionRepository $rentalSubscriptionRepository,
    ) {
    }

    #[Route('/abonnement/confirm/{rentalId}', name: 'app_confirm_subscription')]
    public function __invoke(Request $request, #[MapEntity(mapping: ['rentalId' => 'id'])] Rental $rental): Response
    {
        try {
            $paymentIntent = $this->stripeService->retrievePaymentIntent((string) $request->query->get('payment_intent'));
            $status = $paymentIntent->status;
            if ('succeeded' === $status) {
                try {
                    $rentalSubscription = $this->rentalSubscriptionRepository->findSubscriptionForRental((string) $rental->getId());

                    $chargeId = $paymentIntent->latest_charge;
                    if (null === $chargeId) {
                        return $this->redirectWithError($rental);
                    }

                    $rentalSubscription = $rentalSubscription->pay($rentalSubscription->getSubscription(), $chargeId);
                    $rental->addSubscription($rentalSubscription);
                    $this->rentalSubscriptionRepository->flush();
                } catch (RentalSubscriptionNotFound) {
                    return $this->redirectWithError($rental);
                }
            }

            return $this->render('subscription/confirm.html.twig', [
                'message' => ConfirmationMessage::fromPaymentIntentStatus($status),
            ]);
        } catch (ApiErrorException) {
            return $this->render('subscription/error.html.twig');
        }
    }

    private function redirectWithError(Rental $rental): Response
    {
        $this->addFlash('error', 'Nous avons eu un soucis lors de l\'enregistrement de votre commande. Veuillez nous contacter.');

        return $this->redirect($this->generateUrl('app_get_subscription', ['rental_id' => $rental->getId()]));
    }
}
