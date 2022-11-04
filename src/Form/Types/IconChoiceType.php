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
        $view->vars['svg'] = $options['svg'];
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['icon', 'svg']);
        $resolver->setDefault('icon', null);
        $resolver->setDefault('svg', false);

        parent::configureOptions($resolver);
    }
}