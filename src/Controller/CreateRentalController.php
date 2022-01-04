<?php

namespace App\Controller;

use App\Domain\Rental\DTO\ConfigurationDTO;
use App\Domain\Rental\Service\RentalConfigurationService;
use App\Domain\Rental\Service\RentalDescriptionService;
use App\Domain\Rental\Service\RentalService;
use App\Entity\Rental\Address;
use App\Entity\Rental\Description;
use App\Form\Rental\RentalAddressType;
use App\Form\Rental\RentalDescriptionType;
use App\Form\Rental\RentalFurnituresType;
use App\Form\Rental\RentalInformationsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/deposez-votre-annonce')]
class CreateRentalController extends AbstractController
{
    private RentalService $rentalService;

    public function __construct(RentalService $rentalService)
    {
        $this->rentalService = $rentalService;
    }

    #[Route('/configuration', name: 'app_create_rental')]
    public function index(
        Request $request,
        RentalConfigurationService $configurationService
    ): Response {
        $rental = $this->rentalService->findOrCreateRental($this->getUser());

        $form = $this->createForm(
            RentalInformationsType::class,
            null !== $rental->getConfiguration() ? ConfigurationDTO::fromEntity($rental->getConfiguration()) : new ConfigurationDTO()
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $configurationService->createConfiguration($rental, $form->getData());
            } catch (\Exception $e) {
                $form->addError(new FormError('Nous avons eu un probleme lors de l\'enregistrement de la configuration de votre logement.'));

                return $this->renderForm('create_rental/configuration.html.twig', [
                    'form' => $form,
                ]);
            }

            return $this->redirectToRoute('app_create_rental_furnitures');
        }

        return $this->renderForm('create_rental/configuration.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/equipements', name: 'app_create_rental_furnitures')]
    public function furnitures(
        Request $request
    ): Response {
        $rental = $this->rentalService->findOrCreateRental($this->getUser());

        $form = $this->createForm(RentalFurnituresType::class, $rental);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->rentalService->saveEntity($form->getData());

            return $this->redirectToRoute('app_create_rental_description');
        }

        return $this->renderForm('create_rental/furnitures.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/description', name: 'app_create_rental_description')]
    public function description(
        Request $request,
        RentalDescriptionService $descriptionService,
    ): Response {
        $rental = $this->rentalService->findOrCreateRental($this->getUser());

        $form = $this->createForm(RentalDescriptionType::class, $rental->getDescription() ?? new Description());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->rentalService->saveDescription($rental, $form->getData());

            return $this->redirectToRoute('app_create_rental_address');
        }

        return $this->renderForm('create_rental/description.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/adresse', name: 'app_create_rental_address')]
    public function address(
        Request $request,
        RentalDescriptionService $descriptionService,
    ): Response {
        $rental = $this->rentalService->findOrCreateRental($this->getUser());

        $form = $this->createForm(RentalAddressType::class, $rental->getAddress() ?? new Address());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->rentalService->saveAddress($rental, $form->getData());

            return $this->redirectToRoute('app_create_rental_description');
        }

        return $this->renderForm('create_rental/address.html.twig', [
            'form' => $form,
        ]);
    }
}
