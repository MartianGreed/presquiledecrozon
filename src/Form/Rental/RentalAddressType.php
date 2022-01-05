<?php

namespace App\Form\Rental;

use App\Entity\Data\Town;
use App\Entity\Rental\Address;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RentalAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('address', TextType::class)
            ->add('address2', TextType::class, [
                'required' => false,
            ])
            ->add('town', EntityType::class, [
                'class' => Town::class,
                'choice_label' => static fn (Town $town) => sprintf('(%s) %s', $town->getPostalCode()->getCode(), $town->getName())
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
