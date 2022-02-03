<?php

namespace App\Controller\Profile;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GetFavoriteRentalListController extends AbstractController
{
    #[Route('/mon-compte/coup-de-coeur', name: 'app_profile_favorites')]
    public function __invoke(Request $request): Response
    {
        return $this->render('profile/favorite_rental_list.html.twig');
    }
}