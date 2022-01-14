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
        $builder
            ->add('cover', MediaType::class, [
                'row_attr' => ['class' => 'dropzone-item last'],
                'attr' => ['placeholder' => '+'],
            ])
            ->add('pictures', CollectionType::class, [
                'entry_type' => MediaType::class,
                'entry_options' => [
                    'row_attr' => ['class' => 'dropzone-item'],
                    'label' => false,
                    'attr' => ['placeholder' => '+'],
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
        ]);
    }
}
