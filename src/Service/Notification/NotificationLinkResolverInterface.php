<?php

namespace App\Service\Notification;

use App\Domain\Notifications;

interface NotificationLinkResolverInterface
{
    public function resolve(object $entity): ResolvedNotificationLink;
    public function supports(): Notifications;
}