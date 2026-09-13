<?php

namespace App\Domain\Booking\Exception;

final class CannotManagerOtherOwnersRentalException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Vous ne pouvez pas gérer les locations d\'une autre personne.');
    }
}
