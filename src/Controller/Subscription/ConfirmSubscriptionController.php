<?php

namespace App\Controller\Subscription;

use App\Domain\Exception\RentalSubscriptionNotFound;
use App\Domain\Subscription\ConfirmationMessage;
use App\Entity\Rental\Rental;
use App\Repository\Subscription\RentalSubscriptionRepository;
use App\Service\StripeService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Stripe\Charge;
use Stripe\Exception\ApiErrorException;
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
    #[MapEntity('rental', mapping: ['rentalId' => 'id'])]
    public function __invoke(Request $request, Rental $rental): Response
    {
        try {
            $paymentIntent = $this->stripeService->retrievePaymentIntent((string) $request->query->get('payment_intent'));
            $status = $paymentIntent->status;
            if ('succeeded' === $status) {
                try {
                    $rentalSubscription = $this->rentalSubscriptionRepository->findSubscriptionForRental((string) $rental->getId());
                    /** @var ?Charge $charge */
                    $charge = $paymentIntent->charges->first();
                    if (null === $charge) {
                        return $this->redirectWithError($rental);
                    }

                    $rentalSubscription = $rentalSubscription->pay($rentalSubscription->getSubscription(), $charge->id);
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
