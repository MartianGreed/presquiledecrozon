<?php

namespace App\Infrastructure\Symfony\EventSubscriber;

use App\Entity\User;
use App\Repository\Rental\RentalRepository;
use App\Service\ApplicationTokenGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class CheckRentalTokenSubscriber implements EventSubscriberInterface
{
    private const CREATE_RENTAL_PATH_PREFIX = '/deposez-votre-annonce';
    private const PREVIEW_RENTAL_PATH = '/previsualisation/annonce';

    public function __construct(
        private readonly ApplicationTokenGenerator $applicationTokenGenerator,
        private readonly RentalRepository $rentalRepository,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [ControllerEvent::class => ['onKernelController', 125]];
    }

    final public function onKernelController(ControllerEvent $event): ?Response
    {
        if (!(
            !str_starts_with($event->getRequest()->getPathInfo(), self::CREATE_RENTAL_PATH_PREFIX)
            xor !str_starts_with($event->getRequest()->getPathInfo(), self::PREVIEW_RENTAL_PATH)
        )
        ) {
            return null;
        }

        $request = $event->getRequest();

        $id = $request->query->get('id');
        $token = $request->query->get('token');
        /** @var ?User $user */
        $user = $this->tokenStorage->getToken()?->getUser();


        if (null === $user) {
            return null;
        }

        if (null === $token && null === $id) {
            return null;
        }

        $rental = $this->rentalRepository->find($id);

        if (
            $token !== $this->applicationTokenGenerator->generateToken((string) $id)
            || $rental?->getOwner()?->getId() !== $user->getId()
        ) {
            throw new \Exception('Forbidden', 403);
        }

        $request->attributes->set('rental', $rental);

        return null;
    }
}
