<?php

namespace App\Infrastructure\Symfony\Formatter;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

final class FormErrorsToArrayFormatter
{
    /**
     * @return array<int|string, string|array<mixed>>
     */
    public static function format(FormInterface $form): array
    {
        $errors = [];
        /** @var FormError $error */
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            $childErrors = self::format($childForm);
            $errors[$childForm->getName()] = $childErrors;
        }

        return $errors;
    }
}
