<?php

namespace App\Infrastructure\Symfony\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class UniqueUser extends Constraint
{
    public string $message = 'Impossible d\'utiliser cet email.';
}
