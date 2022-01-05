<?php

namespace App\Form\Types;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;

final class CounterType extends IntegerType
{
    public function getBlockPrefix(): string
    {
        return 'counter';
    }
}
