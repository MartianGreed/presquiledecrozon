<?php

namespace App\Controller;

use App\Domain\Rental\DTO\ConfigurationDTO;
use App\Domain\Rental\Service\RentalConfigurationService;
use App\Domain\Rental\Service\RentalService;
use App\Entity\Rental\Address;
use App\Entity\Rental\Description;
use App\Entity\Rental\Rental;
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
    use WithUserTrait;

    public function __construct(private readonly RentalService $rentalService)
    {
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
                /** @var ConfigurationDTO $data */
                $data = $form->getData();
                $configurationService->createConfiguration($rental, $data);
            } catch (\Exception) {
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
            /** @var Rental $data */
            $data = $form->getData();
            $this->rentalService->saveEntity($data);

            return $this->redirectToRoute('app_create_rental_description');
        }

        return $this->renderForm('create_rental/furnitures.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/description', name: 'app_create_rental_description')]
    public function description(Request $request): Response
    {
        $rental = $this->rentalService->findOrCreateRental($this->getUser());

        $form = $this->createForm(RentalDescriptionType::class, $rental->getDescription() ?? new Description());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Description $data */
            $data = $form->getData();
            $this->rentalService->saveDescription($rental, $data);

            return $this->redirectToRoute('app_create_rental_address');
        }

        return $this->renderForm('create_rental/description.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/adresse', name: 'app_create_rental_address')]
    public function address(Request $request): Response
    {
        $rental = $this->rentalService->findOrCreateRental($this->getUser());

        $form = $this->createForm(RentalAddressType::class, $rental->getAddress() ?? new Address());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Address $data */
            $data = $form->getData();
            $this->rentalService->saveAddress($rental, $data);

            return $this->redirectToRoute('app_create_rental_description');
        }

        return $this->renderForm('create_rental/address.html.twig', [
            'form' => $form,
        ]);
    }
}
