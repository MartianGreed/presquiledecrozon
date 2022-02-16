<?php

namespace App\Form;

use App\Entity\Rental\Rental;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class BookRentalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Rental $rental */
        $rental = $options['rental'];

        $choices = [];
        for ($i = 1; $i <= $rental->getConfiguration()?->getPeopleCount(); $i++) {
            $choices[$i] = $i;
        }

        $builder
            ->add('startAt', DateType::class, [
                'html5' => true,
                'widget' => 'single_text',
            ])
            ->add('endAt', DateType::class, [
                'html5' => true,
                'widget' => 'single_text',
            ])
            ->add('peopleCount', ChoiceType::class, [
                'choices' => $choices,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('rental');
        $resolver->setAllowedTypes('rental', Rental::class);
    }
}