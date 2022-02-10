<?php

namespace App\Controller\Rental;

use App\Repository\Rental\RentalRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ListRentalsController extends AbstractController
{
    public function __construct(
        private readonly RentalRepository $rentalRepository,
        private readonly PaginatorInterface $paginator,
    )
    {
    }

    #[Route('/annonces', name: 'app_rental_list')]
    public function __invoke(
        Request $request,
    ): Response {
        $query = $this->rentalRepository->getPaginatedList();

        return $this->render('page/list-rental.html.twig', [
            'pagination' => $this->paginator->paginate($query, (int) $request->query->get('page', 1), 25),
        ]);
    }
}