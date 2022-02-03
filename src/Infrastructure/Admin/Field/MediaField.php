<?php

namespace App\Infrastructure\Admin\Field;

use App\Form\Types\MediaType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

final class MediaField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplatePath('admin/field/media.html.twig')
            ->setFormType(MediaType::class)
            ->setCssClass('field-media')
        ;
    }
}