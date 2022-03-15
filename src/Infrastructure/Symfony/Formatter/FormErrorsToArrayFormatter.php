<?php

namespace App\Infrastructure\Symfony\Formatter;

use Symfony\Component\Form\FormInterface;

final class FormErrorsToArrayFormatter
{
    /** @return array{array<string, string>} */
    public static function format(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if (!$childForm instanceof FormInterface) {
                continue;
            }

            if ($childErrors = self::format($childForm)) {
                $errors[$childForm->getName()] = $childErrors;
            }
        }

        return $errors;
    }
}