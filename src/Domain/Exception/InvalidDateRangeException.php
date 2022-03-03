<?php

namespace App\Domain\Exception;

final class InvalidDateRangeException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Start date cannot be after end date');
    }
}
