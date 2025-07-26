<?php

namespace App\Infrastructure\Symfony\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class UploadPictureConstraint extends Constraint
{
    public string $message = 'Vous devez renseigner l\'index auquel la photo sera ajoutée';

    /**
     * @return array<string>
     */
    public function getTargets(): array
    {
        return [Constraint::CLASS_CONSTRAINT];
    }
}
