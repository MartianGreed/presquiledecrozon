<?php

namespace App\Infrastructure\Admin\Form;

use App\Entity\Data\Bed;
use App\Entity\Rental\Bedroom;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AdminBedroomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('beds', CollectionType::class, [
            'entry_type' => EntityType::class,
            'entry_options' => [
                'class' => Bed::class,
            ],
            'allow_add' => true,
            'allow_delete' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bedroom::class,
        ]);
    }
}