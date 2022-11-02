<?php

namespace App\Form\Types;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class IconChoiceType extends ChoiceType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['icon'] = $options['icon'];
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined('icon');
        $resolver->setDefault('icon', null);

        parent::configureOptions($resolver);
    }
}