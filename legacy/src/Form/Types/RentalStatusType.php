<?php

namespace App\Form\Types;

use App\Domain\Rental\Status;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RentalStatusType extends ChoiceType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => [
                Status::DRAFT->value => 'Brouillon',
                Status::IN_PROGRESS->value => 'En cours',
                Status::VALID->value => 'Valide',
                Status::PUBLISHED->value => 'Publiée',
                Status::DISABLED->value => 'Désactivée',
            ],
        ]);
    }
}
