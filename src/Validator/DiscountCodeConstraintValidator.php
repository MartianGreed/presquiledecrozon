<?php

namespace App\Validator;

use App\Entity\Subscription\Discount;
use App\Repository\Subscription\DiscountRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class DiscountCodeConstraintValidator extends ConstraintValidator
{
    public function __construct(private readonly DiscountRepository $discountRepository)
    {
    }

    /** @param DiscountCodeConstraint $constraint */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        /** @var ?Discount $discount */
        $discount = $this->discountRepository->findOneBy(['code' => $value]);
        if (null === $discount) {
            $this->buildViolation(strval($value), $constraint->existsMessage);
            return;
        }

        if ($discount->getExpiresAt() < new \DateTime('now')) {
            $this->buildViolation(strval($value), $constraint->expiredMessage);
        }

        if (
            null !== $discount->getPayee()
            && $discount->getPayee()->getId() !== $constraint->payeeId
        ) {
            $this->buildViolation(strval($value), $constraint->existsMessage);
        }
    }

    private function buildViolation(string $value, string $message): void
    {
        $this->context->buildViolation($message)
                      ->setParameter('{{ code }}', $value)
                      ->addViolation()
        ;
    }
}