<?php

namespace App\Form\Rental;

use App\Domain\Rental\DTO\ConfigurationDTO;
use App\Entity\Data\RentalType;
use App\Form\Types\BedroomType;
use App\Form\Types\CounterType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RentalInformationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rentalType', EntityType::class, [
                'class'        => RentalType::class,
                'choice_label' => 'label',
                'expanded'     => true,
                'multiple'     => false,
            ])
            ->add('peopleCount', CounterType::class)
            ->add('bedroomCount', CounterType::class)
            ->add('bedrooms', CollectionType::class, [
                'entry_type' => BedroomType::class,
                'entry_options' => [
                    'label' => 'Chambre __field_index__',
                ],
                'allow_add' => true,
                'allow_delete' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ConfigurationDTO::class,
        ]);
    }
}