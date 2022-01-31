<?php

namespace App\Controller\Subscription;

use App\Domain\Subscription\ConfirmationMessage;
use App\Entity\Rental\Rental;
use App\Service\StripeService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ConfirmSubscriptionController extends AbstractController
{
    public function __construct(private readonly StripeService $stripeService)
    {
    }

    #[Route('/abonnement/confirm/{rentalId}', name: 'app_confirm_subscription')]
    #[ParamConverter('rental', class: Rental::class, options: ['id' => 'rentalId'])]
    public function __invoke(Request $request, Rental $rental): Response
    {
        try {
            $paymentIntent = $this->stripeService->retrievePaymentIntent((string) $request->query->get('payment_intent'));
            dd($paymentIntent, $rental);
            return $this->render('subscription/confirm.html.twig', [
                'message' => ConfirmationMessage::fromPaymentIntentStatus($paymentIntent->status),
            ]);
        } catch (ApiErrorException $e) {
            return $this->render('subscription/error.html.twig');
        }
    }
}