<?php

namespace App\Domain\Subscription;

enum SubscriptionStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
}
