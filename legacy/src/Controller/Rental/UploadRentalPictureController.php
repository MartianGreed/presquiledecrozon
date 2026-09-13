<?php

namespace App\Controller\Rental;

use App\Controller\WithUserTrait;
use App\Domain\Exception\RentalNotFoundException;
use App\Domain\Rental\DTO\UploadedPicture;
use App\Entity\Rental\Rental;
use App\Form\Rental\UploadRentalPictureType;
use App\Infrastructure\Symfony\Formatter\FormErrorsToArrayFormatter;
use App\Repository\Rental\RentalRepository;
use App\Service\RentalPictureService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class UploadRentalPictureController extends AbstractController
{
    use WithUserTrait;

    public function __construct(
        private readonly RentalRepository $rentalRepository,
        private readonly RentalPictureService $rentalPictureService,
    ) {
    }

    #[Route('/rental/upload/{id}', name: 'app_rental_upload_picture', methods: [Request::METHOD_POST])]
    public function __invoke(Request $request, string $id): Response
    {
        try {
            /** @var Rental|null $rental */
            $rental = $this->rentalRepository->find($id);
            if (null === $rental) {
                throw new RentalNotFoundException($id);
            }
        } catch (RentalNotFoundException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        $form = $this->createForm(UploadRentalPictureType::class, new UploadedPicture(), [
            'csrf_protection' => false,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedPicture $picture */
            $picture = $form->getData();
            $rental = $this->rentalPictureService->uploadPicture($rental, $picture);

            return new JsonResponse([
                'message' => 'Picture has properly been upload',
                'rental_id' => $rental->getId(),
                'picture_id' => $picture->media->getId(),
                'is_valid' => $rental->getGallery()?->getCover() instanceof \App\Entity\Media && 5 === $rental->getGallery()->getPictures()->count(),
            ]);
        }

        if (! $form->isValid()) {
            return new JsonResponse([
                'type' => 'validation_errors',
                'title' => 'There was an error validating form',
                'errors' => FormErrorsToArrayFormatter::format($form),
                'is_valid' => false,
            ], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'message' => 'Route is working',
        ]);
    }
}
