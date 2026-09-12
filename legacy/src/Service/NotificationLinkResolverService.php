<?php

namespace App\Service;

use App\Domain\Notifications;
use App\Entity\Notification;
use App\Service\Notification\NotificationLinkResolverInterface;
use App\Service\Notification\ResolvedNotificationLink;

final class NotificationLinkResolverService
{
    /**
     * @var array<NotificationLinkResolverInterface>
     */
    private array $resolvers;

    /**
     * @param array<NotificationLinkResolverInterface> $resolvers
     */
    public function __construct(iterable $resolvers)
    {
        foreach ($resolvers as $resolver) {
            $this->resolvers[$resolver->supports()->name] = $resolver;
        }
    }

    public function resolve(Notification $entity): ResolvedNotificationLink
    {
        return match (Notifications::tryFrom($entity->getLabel())) {
            Notifications::RENTAL_HAS_BEEN_PUBLISHED => $this->resolvers[Notifications::RENTAL_HAS_BEEN_PUBLISHED->name]->resolve($entity),
            Notifications::RENTAL_HAS_BEEN_BOOKED => $this->resolvers[Notifications::RENTAL_HAS_BEEN_BOOKED->name]->resolve($entity),
            Notifications::BOOKING_HAS_BEEN_CONFIRMED => $this->resolvers[Notifications::BOOKING_HAS_BEEN_CONFIRMED->name]->resolve($entity),
            Notifications::BOOKING_HAS_BEEN_CANCELLED => $this->resolvers[Notifications::BOOKING_HAS_BEEN_CANCELLED->name]->resolve($entity),
            default => throw new \LogicException('Not implemented yet'),
        };
    }
}
