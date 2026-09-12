<?php

namespace App\Controller\Profile;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/mon-compte')]
final class ProfileController extends AbstractController
{
    #[Route('', name: 'app_profile')]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig');
    }

    // /mon-compte/informations
    // /mon-compte/locations
    // /mon-compte/messagerie
    // /mon-compte/reservations
    // /mon-compte/coup-de-coeur
}
