<?php

namespace App\Domain\Subscription;

final class ConfirmationMessage
{
    public static function fromPaymentIntentStatus(string $status): string
    {
        return match ($status) {
            'succeeded' => 'Félicitations, votre abonnement a bien été créé, vous pouvez désormais publier votre annonce',
            'processsing' => 'Votre paiement est cours de traitement, vous recevrez des nouvelles dès que celui-ci sera validé',
            'requires_payment_method' => 'Votre paiement a échoué. Veuillez utiliser un autre moyen de paiement',
            default => 'Nous avons rencontré, veuillez nous contacter.',
        };
    }
}
