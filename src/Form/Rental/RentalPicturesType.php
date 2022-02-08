<?php

namespace App\Form\Rental;

use App\Entity\Rental\Gallery;
use App\Form\Types\MediaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RentalPicturesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isRequired = !$options['is_update'];

        $builder
            ->add('cover', MediaType::class, [
                'row_attr' => ['class' => 'dropzone-item last'],
                'attr' => ['placeholder' => '+'],
                'required' => $isRequired,
            ])
            ->add('pictures', CollectionType::class, [
                'entry_type' => MediaType::class,
                'entry_options' => [
                    'row_attr' => ['class' => 'dropzone-item'],
                    'label' => false,
                    'attr' => ['placeholder' => '+'],
                    'required' => $isRequired,
                ],
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Gallery::class,
            'is_update' => false,
        ]);

        $resolver->setAllowedTypes('is_update', 'bool');
    }
}
