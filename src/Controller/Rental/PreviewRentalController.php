<?php

declare(strict_types=1);

namespace App\Controller\Rental;

use App\Entity\Rental\Rental;
use App\Form\Booking\BookRentalType;
use App\Repository\Booking\BookingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class PreviewRentalController extends AbstractController
{
    public function __construct(private readonly BookingRepository $bookingRepository)
    {}

    #[Route('/previsualisation/annonce', name: 'app_preview_rental')]
    public function __invoke(Request $request, ?Rental $rental): Response
    {
        if (null === $rental) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(BookRentalType::class, null, [
            'rental' => $rental,
            'action' => $this->generateUrl('app_rental_details', ['slug' => $rental->getSlug()])
        ]);

        return $this->render('page/rental-detail.html.twig', [
            'rental' => $rental,
            'form' => $form,
            'bookings' => $this->bookingRepository->getBookingRanges((string) $rental->getId()),
        ]);
    }
}