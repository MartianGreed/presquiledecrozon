<?php

namespace App\Form\Rental;

use App\Domain\Rental\DTO\UploadedPicture;
use App\Form\Types\MediaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UploadRentalPictureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('field', ChoiceType::class, [
                'choices' => [
                    'cover' => 'cover',
                    'picture' => 'picture',
                ],
                'required' => true,
            ])
            ->add('media', MediaType::class, [
                'required' => true,
            ])
            ->add('index', IntegerType::class)
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UploadedPicture::class,
        ]);
    }
}