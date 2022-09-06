<?php

namespace App\Service\Notification;

use App\Domain\Notifications;
use App\Entity\Notification;

interface NotificationLinkResolverInterface
{
    public function resolve(Notification $entity): ResolvedNotificationLink;
    public function supports(): Notifications;
}