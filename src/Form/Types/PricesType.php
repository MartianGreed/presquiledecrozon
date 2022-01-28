<?php

namespace App\Form\Types;

use App\Entity\Rental\Price;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PricesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rangeStart', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('rangeEnd', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('dailyRate', MoneyType::class)
            ->add('weeklyRate', MoneyType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Price::class,
        ]);
    }
}