<?php

namespace App\Controller\Profile;

use App\Controller\WithUserTrait;
use App\Repository\FavoriteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class IsRentalInFavoriteListController extends AbstractController
{
    use WithUserTrait;

    public function __construct(
        private readonly FavoriteRepository $favoriteRepository
    )
    {
    }

    #[Route('/mon-compte/is-favorite/{id}', name: 'app_profile_is_rental_favorite', methods: [Request::METHOD_GET])]
    public function __invoke(Request $request, string $id): Response
    {
        if (! $this->favoriteRepository->isRentalInUserFavoriteList($id, (string) $this->getUser()->getId())) {
            return new JsonResponse([
                'favorite' => false,
            ], Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse([
            'favorite' => $id,
        ]);
    }
}
