<?php

namespace App\Controller\Profile;

use App\Controller\WithUserTrait;
use App\Repository\FavoriteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GetFavoriteRentalListController extends AbstractController
{
    use WithUserTrait;

    public function __construct(private readonly FavoriteRepository $favoriteRepository)
    {
    }

    #[Route('/mon-compte/coups-de-coeur', name: 'app_profile_favorites')]
    public function __invoke(Request $request): Response
    {
        return $this->render('profile/favorite_rental_list.html.twig', [
            'rental_list' => $this->favoriteRepository->getUserFavoritesList((string) $this->getUser()->getId()),
        ]);
    }
}
