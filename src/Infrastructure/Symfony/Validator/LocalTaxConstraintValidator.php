<?php

namespace App\Infrastructure\Symfony\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class LocalTaxConstraintValidator extends ConstraintValidator
{
    /** @param LocalTaxConstraint $constraint */
    public function validate(mixed $value, Constraint $constraint): void
    {
        $matches = [];
        $stringValue = is_scalar($value) || is_null($value) ? (string) $value : '';
        if (!preg_match_all('/(\d+([,.])\d+)([%€])/u', $stringValue, $matches)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
