<?php

namespace App\Form\Profile;

use App\Entity\Profile;
use App\Form\Types\MediaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

final class UpdateProfileInformationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
            ->add('gender', ChoiceType::class, [
                'choices' => ['M' => 'M', 'Mme' => 'F'],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('cellphone', TextType::class, [
                'constraints' => [
                    new Regex(
                        pattern: '/^(\+33[0-9]{1}|(0[0-9]{1}))[0-9]{8}$/',
                        message: 'Ce numéro de téléphone n\'est pas valide.'
                    ),
                    new Length(min: 10, max: 13),
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('birthdate', DateType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('picture', MediaType::class, [
                'required' => false,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}
