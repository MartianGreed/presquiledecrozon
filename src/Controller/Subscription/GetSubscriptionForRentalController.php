<?php

namespace App\Controller\Subscription;

use App\Controller\WithUserTrait;
use App\Domain\Exception\DefaultSubscriptionNotFound;
use App\Domain\Exception\RentalNotFoundException;
use App\Domain\Exception\RentalSubscriptionNotFound;
use App\Domain\Rental\Service\RentalService;
use App\Domain\Subscription\DiscountService;
use App\Domain\Subscription\Repository\SubscriptionRepository;
use App\Entity\Subscription\RentalSubscription;
use App\Form\ApplyDiscountType;
use App\Form\CreateSubscriptionType;
use App\Repository\Subscription\RentalSubscriptionRepository;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GetSubscriptionForRentalController extends AbstractController
{
    use WithUserTrait;

    public function __construct(
        private readonly RentalService $rentalService,
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly StripeService $stripeService,
        private readonly RentalSubscriptionRepository $rentalSubscriptionRepository,
        private readonly DiscountService $discountService,
    ) {
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

            try {
                $subscription = $this->subscriptionRepository->findDefaultSubscription();
            } catch (DefaultSubscriptionNotFound $e) {
                return $this->render('subscription/default-not-found.html.twig');
            }

            try {
                $rentalSubscription = $this->rentalSubscriptionRepository->findSubscriptionForRental((string) $rental->getId());
            } catch (RentalSubscriptionNotFound $e) {
                $rentalSubscription = RentalSubscription::new($subscription, $rental);
                $this->rentalSubscriptionRepository->save($rentalSubscription);
            }

            // Handle discount form
            $discountForm = $this->createForm(
                ApplyDiscountType::class,
                ['discount' => $rentalSubscription->getDiscount()],
                ['payee_id' => $this->getUser()->getId()]
            );
            $discountForm->handleRequest($request);
            if ($discountForm->isSubmitted() && $discountForm->isValid()) {
                $discountCode = $discountForm->get('discount')->getData();
                if (!is_string($discountCode)) {
                    throw new \RuntimeException('Discount code must be a string');
                }
                $this->discountService->applyDiscountCode($rentalSubscription, $discountCode);
                return $this->redirect($this->generateUrl('app_get_subscription', ['rental_id' => $rental->getId()]));
            }

            // Only create form, payment is handled by stripe
            $form = $this->createForm(CreateSubscriptionType::class);

            return $this->render('subscription/create.html.twig', [
                'rental' => $rental,
                'subscription' => $subscription,
                'rental_subscription' => $rentalSubscription,
                'expirationDate' => $subscription->getSubscriptionExpirationDate(new \DateTime('now')),
                'form' => $form,
                'discount_form' => $discountForm,
                'payment_intent' => $this->stripeService->createSubscriptionPaymentIntent($rentalSubscription),
            ]);
        } catch (RentalNotFoundException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }
    }
}
