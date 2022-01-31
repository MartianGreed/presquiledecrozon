<?php

namespace App\Domain\Exception;

final class DefaultSubscriptionNotFound extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Default subscription not found, please contact administrator.');
    }
}