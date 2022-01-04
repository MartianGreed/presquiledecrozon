<?php

namespace App\Validator;

use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class UniqueUserValidator extends ConstraintValidator
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function validate(mixed $value, Constraint $constraint)
    {
        $foundUser = $this->userRepository->findOneBy(['email' => $value]);

        if (null === $foundUser) return;

        $this->context->buildViolation($constraint->message)->addViolation();
    }
}