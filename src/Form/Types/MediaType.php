<?php

namespace App\Form\Types;

use App\Entity\Media;
use App\Service\MediaService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Vich\UploaderBundle\Form\Type\VichImageType;

final class MediaType extends AbstractType
{
    public function __construct(private readonly MediaService $mediaService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $imageUri = fn (Media $media) => $this->mediaService->assetHelper($media, 'file', null, ['h' => $options['h'], 'w' => $options['w']]);

        $builder->add('file', VichImageType::class, [
            'allow_delete' => true,
            'asset_helper' => true,
            'download_uri' => $imageUri,
            'image_uri' => $imageUri,
            'required' => $options['required'],
            'constraints' => [
                new Image(maxSize: '5M'),
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
            'compound' => true,
            'required' => true,
            'h' => 150,
            'w' => 150,
        ]);

        $resolver->setAllowedTypes('h', 'int');
        $resolver->setAllowedTypes('w', 'int');
    }
}
