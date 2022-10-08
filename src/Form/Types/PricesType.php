<?php

namespace App\Form\Types;

use App\Entity\Rental\Price;
use App\Infrastructure\Admin\Form\PriceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PricesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rangeStart', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Début',
            ])
            ->add('rangeEnd', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fin'
            ])
            ->add('dailyRate', PriceType::class, [
                'label' => 'Prix à la nuitée'
            ])
            ->add('weeklyRate', PriceType::class, [
                'label' => 'Prix à la semaine'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Price::class,
        ]);
    }
}
