<?php

namespace App\Domain\Booking\Exception;

final class CannotBookOwnRentalException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Vous ne pouvez pas réserver votre propre location. Pour bloquer des dates, utilisez les périodes de non disponibilités.');
    }
}