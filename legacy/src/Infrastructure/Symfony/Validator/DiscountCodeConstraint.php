<?php

namespace App\Infrastructure\Symfony\Validator;

use Symfony\Component\Validator\Constraint;

final class DiscountCodeConstraint extends Constraint
{
    public string $existsMessage = 'Ce code promo n\'existe pas !';

    public string $expiredMessage = 'Ce code promo n\'est plus valide.';

    public function __construct(
        public readonly string $payeeId
    )
    {
        parent::__construct();
    }
}
