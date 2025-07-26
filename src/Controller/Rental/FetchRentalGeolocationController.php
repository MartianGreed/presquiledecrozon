<?php

namespace App\Controller\Rental;

use App\Domain\Exception\RentalNotFoundException;
use App\Domain\Rental\Service\RentalService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class FetchRentalGeolocationController extends AbstractController
{
    public function __construct(private readonly RentalService $rentalService)
    {
    }

    #[Route('/api/rental/fetch_geolocation/{id}', name: 'app_rental_geolocation')]
    public function __invoke(Request $request, string $id): JsonResponse
    {
        try {
            $rental = $this->rentalService->findRental($id);

            // Workaround to render an empty response as geolocation has not been fetched yet
            if (null === $rental->getGeolocation()) {
                throw new RentalNotFoundException($id);
            }
        } catch (RentalNotFoundException) {
            return new JsonResponse([], Response::HTTP_NO_CONTENT);
        }

        return JsonResponse::fromJsonString(\json_encode($rental->getGeolocation()->getCoordinates(), JSON_THROW_ON_ERROR));
    }
}
