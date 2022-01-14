<?php

namespace App\Form\Rental;

use App\Domain\Rental\RentalPreferences;
use App\Entity\Rental\Preferences;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RentalPreferencesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('acceptedLastBooking', ChoiceType::class, [
                'choices' => RentalPreferences::acceptedLastBookingChoices(),
            ])
            ->add('maxTimeBeforeBooking', ChoiceType::class, [
                'choices' => RentalPreferences::maxTimeBeforeBookingChoices(),
            ])
            ->add('beginBookingAt', ChoiceType::class, [
                'choices' => RentalPreferences::beginBookingAt(),
            ])
            ->add('endBookingAt', ChoiceType::class, [
                'choices' => RentalPreferences::endBookingAt(),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Preferences::class,
        ]);
    }
}
