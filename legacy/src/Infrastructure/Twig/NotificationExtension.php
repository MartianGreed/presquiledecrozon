<?php

namespace App\Infrastructure\Twig;

use App\Service\NotificationLinkResolverService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class NotificationExtension extends AbstractExtension
{
    public function __construct(
        private readonly NotificationLinkResolverService $notificationLinkResolver,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('notification_link_resolver', [$this->notificationLinkResolver, 'resolve']),
        ];
    }
}
