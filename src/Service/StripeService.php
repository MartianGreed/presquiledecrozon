<?php

namespace App\Service;

use App\Entity\Subscription\Subscription;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

final class StripeService
{
    public function __construct(private readonly StripeClient $stripeClient)
    {
    }

    public function createSubscriptionPaymentIntent(Subscription $subscription): PaymentIntent
    {
        return $this->stripeClient->paymentIntents->create([
            'amount' => $subscription->getAmount(),
            'currency' => 'eur',
            'payment_method_types' => ['card'],
        ]);
    }

    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return $this->stripeClient->paymentIntents->retrieve($paymentIntentId);
    }
}