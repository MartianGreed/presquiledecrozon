<?php

namespace App\Domain\Subscription;

enum DiscountType: string
{
    case PERCENT = '%';
    case FIX = '€';
}
