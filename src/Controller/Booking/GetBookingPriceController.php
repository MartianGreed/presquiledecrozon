<?php

namespace App\Controller\Booking;

use App\Domain\Booking\BookingPriceSimulatorService;
use App\Domain\Booking\BookingRequest;
use App\Entity\Rental\Rental;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GetBookingPriceController extends AbstractController
{
    public function __construct(private readonly BookingPriceSimulatorService $simulatorService)
    {
    }

    #[Route('/booking/price/{id}', name: 'app_booking_price', methods: [Request::METHOD_POST])]
    #[MapEntity('rental')]
    public function __invoke(Request $request, Rental $rental): Response
    {
        try {
            /** @var array{startAt: string, endAt: string, peopleCount: string} $content */
            $content = json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception) {
            return new JsonResponse(['message' => 'JSON incorrectly formatted'], Response::HTTP_NOT_ACCEPTABLE);
        }

        $data = BookingRequest::fromArray([
            'start_at' => $content['startAt'],
            'end_at' => $content['endAt'],
            'people_count' => (int) $content['peopleCount'],
            'rental' => $rental,
        ]);

        $bookingPrice = $this->simulatorService->simulate($data);

        return new JsonResponse(['booking_price' => (string) $bookingPrice]);
    }
}
