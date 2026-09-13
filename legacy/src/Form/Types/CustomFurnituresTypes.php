<?php

namespace App\Form\Types;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CustomFurnituresTypes extends CollectionType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'entry_type' => TextType::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'custom_furnitures';
    }
}
