<?php

namespace App\Form\Rental;

use App\Domain\Rental\DTO\GeolocationDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RentalMapType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lat', NumberType::class, [
                'label' => false,
                'scale' => 15,
            ])
            ->add('lng', NumberType::class, [
                'label' => false,
                'scale' => 15,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GeolocationDTO::class,
        ]);
    }
}
