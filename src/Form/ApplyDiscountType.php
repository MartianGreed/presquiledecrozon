<?php

namespace App\Form;

use App\Infrastructure\Symfony\Validator\DiscountCodeConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ApplyDiscountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('discount', TextType::class, [
            'required' => false,
            'constraints' => [new DiscountCodeConstraint(strval($options['payee_id']))]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['payee_id']);
    }
}