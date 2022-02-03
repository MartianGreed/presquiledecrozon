<?php

namespace App\Infrastructure\Admin\Form;

use App\Infrastructure\Symfony\DataTransformer\PriceToMoneyTransformer;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;

final class PriceType extends MoneyType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->addModelTransformer(new PriceToMoneyTransformer());
    }
}