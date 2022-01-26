<?php

namespace App\Form\Types;

use App\Entity\Rental\Unavailability;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UnavailabilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startAt', DateType::class, [
                'widget' => 'single_text',
                'label' => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
            ])
            ->add('endAt', DateType::class, [
                'widget' => 'single_text',
                'label' => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Unavailability::class
        ]);
    }
}