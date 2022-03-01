<?php

namespace App\Form\Booking;

use App\Entity\Rental\Rental;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;

final class BookRentalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Rental $rental */
        $rental = $options['rental'];
        $peopleCount = $rental->getConfiguration()?->getPeopleCount();

        $preferences = $rental->getPreferences();

        $choices = [];
        for ($i = 1; $i <= $rental->getConfiguration()?->getPeopleCount(); $i++) {
            $choices[$i] = $i;
        }

        $builder
            ->add('startAt', DateType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'constraints' => [
                    new Type(\DateTimeInterface::class),
                    new GreaterThan((new \DateTime('now'))->add(new \DateInterval((string) $preferences?->getAcceptedLastBooking()))),
                ],
            ])
            ->add('endAt', DateType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'constraints' => [
                    new Type(\DateTimeInterface::class),
                    new GreaterThan([
                        'propertyPath' => 'parent.all[startAt].data'
                    ]),
                ]
            ])
            ->add('peopleCount', ChoiceType::class, [
                'choices' => $choices,
                'constraints' => [new Range(min: 1, max: $peopleCount)]
            ])
        ;


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('rental');
        $resolver->setAllowedTypes('rental', Rental::class);
    }
}