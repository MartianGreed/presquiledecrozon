<?php

namespace App\Infrastructure\Symfony\Validator;

use App\Domain\Rental\DTO\UploadedPicture;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class UploadPictureConstraintValidator extends ConstraintValidator
{
    /**
     * @param UploadedPicture $value
     * @param UploadPictureConstraint $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ('picture' === $value->field && null === $value->index) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}