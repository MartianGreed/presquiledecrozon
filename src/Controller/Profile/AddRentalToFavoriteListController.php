<?php

namespace App\Controller\Profile;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class AddRentalToFavoriteListController extends AbstractController
{
    #[Route('/mon-compte/coup-de-coeur', name: 'app_profile_add_rental_to_favorites', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        return $this->redirect((string) $request->headers->get('referer'));
    }
}