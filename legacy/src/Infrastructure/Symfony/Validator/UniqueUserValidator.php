<?php

namespace App\Infrastructure\Symfony\Validator;

use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class UniqueUserValidator extends ConstraintValidator
{
    public function __construct(
        private readonly UserRepository $userRepository
    )
    {
    }

    /**
     * @param UniqueUser $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        $foundUser = $this->userRepository->findOneBy([
            'email' => $value,
        ]);

        if (null === $foundUser) {
            return;
        }

        $this->context->buildViolation($constraint->message)->addViolation();
    }
}
