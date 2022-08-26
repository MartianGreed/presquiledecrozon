<?php

namespace App\Service;

use App\Domain\Notifications;
use App\Entity\Notification;
use App\Service\Notification\NotificationLinkResolverInterface;
use App\Service\Notification\ResolvedNotificationLink;

final class NotificationLinkResolverService
{
    /** @var array<NotificationLinkResolverInterface> */
    private array $resolvers;

    public function __construct(iterable $resolvers)
    {
        foreach ($resolvers as $resolver) {
            $this->resolvers[$resolver->supports()->name] = $resolver;
        }
    }

    public function resolve(object $entity): ResolvedNotificationLink
    {
        if (!$entity instanceof Notification) {
            throw new \DomainException('This resolver only supports entity of class :' . Notification::class);
        }

        return match (Notifications::tryFrom($entity->getLabel())) {
            Notifications::RENTAL_HAS_BEEN_PUBLISHED => $this->resolvers[Notifications::RENTAL_HAS_BEEN_PUBLISHED->name]->resolve($entity),
            default => throw new \LogicException('Not implemented yet'),
        };
    }
}