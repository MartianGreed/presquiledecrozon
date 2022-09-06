<?php

namespace App\Service\Notification;

use App\Controller\Admin\BookingController;
use App\Domain\Notifications;
use App\Entity\Notification;
use App\Repository\Booking\BookingRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

final class BookingHasBeenConfirmedNotificationLinkResolver implements NotificationLinkResolverInterface
{
    public function __construct(
        private readonly AdminUrlGenerator $urlGenerator,
        private readonly BookingRepository $bookingRepository,
    )
    {}

    public function resolve(Notification $entity): ResolvedNotificationLink
    {
        $booking = $this->bookingRepository->find($entity->getTargetId());
        return new ResolvedNotificationLink(
            $this->urlGenerator->setController(BookingController::class)->setAction(Action::DETAIL)->setEntityId($booking?->getId())->generateUrl(),
            'Voir la réservation'
        );
    }

    public function supports(): Notifications
    {
        return Notifications::BOOKING_HAS_BEEN_CONFIRMED;
    }
}