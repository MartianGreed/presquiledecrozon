<?php

namespace App\Infrastructure\Symfony\Validator;

use Symfony\Component\Validator\Constraint;

final class LocalTaxConstraint extends Constraint
{
    public string $message = 'La taxe que vous avez renseigné n\'est pas valide';
}