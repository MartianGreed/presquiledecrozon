<?php

namespace App\Form;

use App\Domain\Subscription\CreateSubscriptionDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CreateSubscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
            ->add('civility', ChoiceType::class, [
                'choices' => ['M' => 'M', 'Mme' => 'F'],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('email', EmailType::class)
            ->add('phoneNumber', TextType::class)
            ->add('address', TextType::class)
            ->add('address2', TextType::class, [
                'required' => false,
            ])
            ->add('town', TextType::class)
            ->add('postalCode', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateSubscriptionDTO::class,
        ]);
    }
}