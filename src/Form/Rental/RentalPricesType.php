<?php

namespace App\Form\Rental;

use App\Entity\Rental\Rental;
use App\Form\Types\PricesType;
use App\Infrastructure\Admin\Form\PriceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RentalPricesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dailyRate', PriceType::class)
            ->add('weeklyRate', PriceType::class)
            ->add('prices', CollectionType::class, [
                'entry_type' => PricesType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'compound' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rental::class,
        ]);
    }
}
