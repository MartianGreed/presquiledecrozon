<?php

namespace App\Controller\Rental;

use App\Domain\Rental\Service\RentalService;
use App\Entity\Rental\Rental;
use App\Message\RentalHasBeenPublished;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

final class TogglePublishRentalController extends AbstractController
{
    public function __construct(
        private readonly RentalService $rentalService,
        private readonly MessageBusInterface $bus,
    ) {
    }

    #[Route('/rental/{id}/toggle-publish', name: 'app_rental_toggle_publish', methods: ['PATCH'])]
    #[ParamConverter('rental', class: Rental::class)]
    public function __invoke(Request $request, Rental $rental): Response
    {
        if (!$rental->isPublishable() && !$rental->isPublished()) {
            return new JsonResponse(['message' => 'Vous ne pouvez pas publier la location. Vous devez avoir un abonnement valide pour pouvoir le faire.'], 403);
        }
        if ($rental->isPublishable() && !$rental->isPublished()) {
            $rental = $rental->publish();

            $this->rentalService->saveEntity($rental);

            $this->bus->dispatch(
                new RentalHasBeenPublished(
                    (string) $rental->getId(),
                    (string) $rental->getActiveSubscription()?->getId(),
                    (new \DateTime('now'))->format('d/m/Y H:m:s')
                )
            );

            return new JsonResponse(['message' => 'Rental has successfully been published']);
        }

        $data = ['message' => 'Ok'];
        if ($rental->isPublished()) {
            $rental = $rental->disable();
            $this->rentalService->saveEntity($rental);
            $data['message'] = 'Rental has successfully been disabled';
        }

        return new JsonResponse($data);
    }
}
