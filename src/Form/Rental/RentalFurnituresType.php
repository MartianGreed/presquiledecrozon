<?php

namespace App\Form\Rental;

use App\Entity\Data\Furniture;
use App\Entity\Rental\Rental;
use App\Form\Types\CustomFurnituresTypes;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RentalFurnituresType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('furnitures', EntityType::class, [
                'class' => Furniture::class,
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('customFurnitures', CustomFurnituresTypes::class, [
                'allow_add' => true,
                'allow_delete' => true,
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
