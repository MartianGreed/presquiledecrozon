<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\{AbstractType, FormBuilderInterface};
use Symfony\Component\Form\Extension\Core\Type\{PasswordType, RepeatedType, SubmitType};

final class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type'            => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent être identiques.',
            ])
            ->add('submit', SubmitType::class)
        ;
    }
}