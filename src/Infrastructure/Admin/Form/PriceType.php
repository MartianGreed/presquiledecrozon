<?php

namespace App\Infrastructure\Admin\Form;

use App\Infrastructure\Symfony\DataTransformer\PriceToMoneyTransformer;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

final class PriceType extends MoneyType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->addModelTransformer(new PriceToMoneyTransformer());
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['row_attr'] = $view->vars['row_attr'] + ['class' => 'money_type'];

        parent::buildView($view, $form, $options);
    }
}
