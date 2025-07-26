<?php

namespace App\Controller\Profile;

use App\Controller\WithUserTrait;
use App\Repository\FavoriteRepository;
use App\Repository\Rental\RentalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class AddRentalToFavoriteListController extends AbstractController
{
    use WithUserTrait;

    public function __construct(
        private readonly RentalRepository $rentalRepository,
        private readonly FavoriteRepository $favoriteRepository,
    ) {
    }

    #[Route('/mon-compte/coup-de-coeur/{id}', name: 'app_profile_add_rental_to_favorites', methods: ['POST'])]
    public function __invoke(Request $request, string $id): Response
    {
        $rental = $this->rentalRepository->find($id);
        if (null === $rental) {
            return new JsonResponse([], Response::HTTP_NO_CONTENT);
        }

        try {
            if ($this->favoriteRepository->addNewFavoriteToList($id, (string) $this->getUser()->getId())) {
                return new JsonResponse([
                    'favorite' => $id,
                ], Response::HTTP_CREATED);
            }

            $this->favoriteRepository->removeFromFavoriteList($id, (string) $this->getUser()->getId());

            return new JsonResponse([
                'removed' => true,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_ACCEPTABLE);
        }
    }
}
