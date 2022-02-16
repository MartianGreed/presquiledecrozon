<?php

namespace App\Controller\Rental;

use App\Domain\Exception\RentalNotFoundException;
use App\Entity\Booking\Booking;
use App\Form\BookRentalType;
use App\Repository\Rental\RentalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RentalDetailController extends AbstractController
{
    public function __construct(private readonly RentalRepository $rentalRepository)
    {
    }

    #[Route('/annonce/{slug}', name: 'app_rental_details')]
    public function __invoke(Request $request, string $slug): Response
    {
        try {
            $rental = $this->rentalRepository->fetchRentalDetails($slug);
        } catch (RentalNotFoundException $e) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(BookRentalType::class, null, [
            'rental' => $rental
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dd($form->getData());
        }

        return $this->renderForm('page/rental-detail.html.twig', [
            'rental' => $rental,
            'form' => $form,
        ]);
    }
}