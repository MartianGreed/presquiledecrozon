<?php

namespace App\Service\Notification;

use App\Domain\Notifications;
use App\Entity\Notification;
use App\Repository\Rental\RentalRepository;
use Symfony\Component\Routing\RouterInterface;

final class RentalHasBeenPublishedNotificationLinkResolver implements NotificationLinkResolverInterface
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly RentalRepository $rentalRepository,
    )
    {}

    public function resolve(Notification $entity): ResolvedNotificationLink
    {
        $rental = $this->rentalRepository->find($entity->getTargetId());
        return new ResolvedNotificationLink(
            $this->router->generate('app_rental_details', ['slug' => $rental?->getSlug()]),
            'Voir l\'annonce'
        );
    }

    public function supports(): Notifications
    {
        return Notifications::RENTAL_HAS_BEEN_PUBLISHED;
    }
}