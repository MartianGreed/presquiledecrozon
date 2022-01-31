<?php

namespace App\Domain\Subscription\Repository;

use App\Entity\Subscription\Subscription;

interface SubscriptionRepository
{
    public function findDefaultSubscription(): Subscription;
}