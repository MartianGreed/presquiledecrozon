<?php

namespace App\Controller;

use App\Repository\Rental\RentalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(RentalRepository $rentalRepository): Response
    {
        return $this->render('page/index.html.twig',            [
            'rentals' => $rentalRepository->findFeatured(),
        ]);
    }
}
