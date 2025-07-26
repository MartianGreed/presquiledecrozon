<?php

namespace App\Service;

use App\Entity\Subscription\RentalSubscription;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

final class StripeService
{
    public function __construct(private readonly StripeClient $stripeClient)
    {
    }

    public function createSubscriptionPaymentIntent(RentalSubscription $subscription): PaymentIntent
    {
        $price = $subscription->getAmount();

        return $this->stripeClient->paymentIntents->create([
            'amount' => $price->getValue(),
            'currency' => $price->getCurrency()->getValue(),
            'payment_method_types' => ['card'],
        ]);
    }

    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return $this->stripeClient->paymentIntents->retrieve($paymentIntentId);
    }
}
