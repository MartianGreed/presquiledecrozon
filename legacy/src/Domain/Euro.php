<?php

namespace App\Domain;

final class Euro extends Currency
{
    public function __construct()
    {
        parent::__construct('eur', '€');
    }
}
