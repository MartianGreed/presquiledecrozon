<?php

namespace App\Form\Booking;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ConfirmBookingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('peopleCount', NumberType::class, [
                'label' => false,
                'data' => $options['peopleCount'],
                'empty_data' => $options['peopleCount'],
            ])
            ->add('ownerMessage', TextareaType::class, [
                'label' => false,
                'data' => $options['ownerMessage'],
                'empty_data' => $options['ownerMessage'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['peopleCount', 'ownerMessage']);
        $resolver->setAllowedTypes('peopleCount', 'int');
        $resolver->setAllowedTypes('ownerMessage', 'string');
    }
}