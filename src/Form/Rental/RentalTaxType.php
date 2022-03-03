<?php

namespace App\Form\Rental;

use App\Entity\Data\Linens;
use App\Entity\Rental\Tax;
use App\Infrastructure\Admin\Form\PriceType;
use App\Infrastructure\Symfony\Validator\LocalTaxConstraint;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RentalTaxType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('localTax', TextType::class, [
                'constraints' => [
                    new LocalTaxConstraint()
                ],
            ])
            ->add('cleaningTax', PriceType::class)
            ->add('linensTax', PriceType::class)
            ->add('linens', EntityType::class, [
                'class' => Linens::class,
                'expanded' => true,
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tax::class,
        ]);
    }
}
