<?php

namespace App\Service\Notification;

final class ResolvedNotificationLink
{
    public function __construct(
        public readonly string $url,
        public readonly string $label,
    ) {}
}