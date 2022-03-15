<?php

namespace App\Controller\Rental;

use App\Controller\WithQueryParamsRedirectTrait;
use App\Controller\WithUserTrait;
use App\Domain\Rental\DTO\ConfigurationDTO;
use App\Domain\Rental\DTO\GeolocationDTO;
use App\Domain\Rental\Service\RentalConfigurationService;
use App\Domain\Rental\Service\RentalService;
use App\Entity\Rental\Address;
use App\Entity\Rental\Condition;
use App\Entity\Rental\Description;
use App\Entity\Rental\Gallery;
use App\Entity\Rental\Preferences;
use App\Entity\Rental\Rental;
use App\Entity\Rental\Tax;
use App\Form\Rental\RentalAddressType;
use App\Form\Rental\RentalConditionsType;
use App\Form\Rental\RentalDescriptionType;
use App\Form\Rental\RentalFurnituresType;
use App\Form\Rental\RentalInformationsType;
use App\Form\Rental\RentalMapType;
use App\Form\Rental\RentalPicturesType;
use App\Form\Rental\RentalPreferencesType;
use App\Form\Rental\RentalPricesType;
use App\Form\Rental\RentalTaxType;
use App\Form\Rental\RentalUnavailabilitiesType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/deposez-votre-annonce')]
final class CreateRentalController extends AbstractController
{
    use WithUserTrait, WithQueryParamsRedirectTrait;

    public function __construct(private readonly RentalService $rentalService)
    {
    }

    #[Route('/configuration', name: 'app_create_rental')]
    public function index(
        Request $request,
        RentalConfigurationService $configurationService,
        ?Rental $rental,
    ): Response {
        if (null === $rental) {
            $rental = $this->rentalService->findOrCreateRental($this->getUser());
        }

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

            return $this->redirectToRouteWithQueryParams('app_create_rental_furnitures', $request->query->all());
        }

        return $this->renderForm('create_rental/configuration.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/equipements', name: 'app_create_rental_furnitures')]
    public function furnitures(
        Request $request,
        ?Rental $rental,
    ): Response {
        if (null === $rental) {
            $rental = $this->rentalService->findOrCreateRental($this->getUser());
        }

        $form = $this->createForm(RentalFurnituresType::class, $rental);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Rental $data */
            $data = $form->getData();
            $this->rentalService->saveEntity($data);

            return $this->redirectToRouteWithQueryParams('app_create_rental_description', $request->query->all());
        }

        return $this->renderForm('create_rental/furnitures.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/description', name: 'app_create_rental_description')]
    public function description(Request $request, ?Rental $rental): Response
    {
        if (null === $rental) {
            $rental = $this->rentalService->findOrCreateRental($this->getUser());
        }

        $form = $this->createForm(RentalDescriptionType::class, $rental->getDescription() ?? new Description());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            assert($data instanceof Description);
            $this->rentalService->saveDescription($rental, $data);

            return $this->redirectToRouteWithQueryParams('app_create_rental_address', $request->query->all());
        }

        return $this->renderForm('create_rental/description.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/adresse', name: 'app_create_rental_address')]
    public function address(Request $request, ?Rental $rental): Response
    {
        if (null === $rental) {
            $rental = $this->rentalService->findOrCreateRental($this->getUser());
        }

        $form = $this->createForm(RentalAddressType::class, $rental->getAddress() ?? new Address());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Address $data */
            $data = $form->getData();
            $this->rentalService->saveAddress($rental, $data);

            return $this->redirectToRouteWithQueryParams('app_create_rental_map', $request->query->all());
        }

        return $this->renderForm('create_rental/address.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/carte', name: 'app_create_rental_map')]
    public function map(Request $request, ?Rental $rental): Response
    {
        if (null === $rental) {
            $rental = $this->rentalService->findOrCreateRental($this->getUser());
        }

        $geolocation = $rental->getGeolocation();
        if (null === $geolocation) {
            throw new \LogicException('Geolocation cannot be null');
        }

        $form = $this->createForm(RentalMapType::class, GeolocationDTO::fromEntity($geolocation), ['allow_extra_fields' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var GeolocationDTO $geolocationDTO */
                $geolocationDTO = $form->getData();
                $this->rentalService->improveLocalisation(
                    $rental,
                    $geolocationDTO,
                    (string) $request->request->get('suggestion', null),
                    (string) $request->request->get('suggestion_meta', null),
                );

                return $this->redirectToRouteWithQueryParams('app_create_rental_pictures', $request->query->all());
            } catch (\Exception) {
                return $this->renderForm('create_rental/map.html.twig', [
                    'form' => $form,
                ]);
            }
        }

        return $this->renderForm('create_rental/map.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/photos', name: 'app_create_rental_pictures')]
    public function pictures(Request $request, ?Rental $rental): Response
    {
        if (null === $rental) {
            $rental = $this->rentalService->findOrCreateRental($this->getUser());
        }

        $form = $this->createForm(RentalPicturesType::class, $rental->getGallery(), ['is_update' => null !== $rental->getGallery()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Gallery $gallery */
            $gallery = $form->getData();

            try {
                $rental = $this->rentalService->savePictures($rental, $gallery);
            } catch (\Exception) {
                return $this->renderForm('create_rental/pictures.html.twig', [
                    'form' => $form,
                ]);
            }

            return $this->redirectToRouteWithQueryParams('app_create_rental_availabilities', $request->query->all());
        }

        return $this->renderForm('create_rental/pictures.html.twig', [
            'form' => $form,
            'rental' => $rental,
            'next_step_url' => $this->getUrl('app_create_rental_availabilities', $request->query->all()),
        ]);
    }

    #[Route('/disponibilites', name: 'app_create_rental_availabilities')]
    public function availabilities(Request $request, ?Rental $rental): Response
    {
        if (null === $rental) {
            $rental = $this->rentalService->findOrCreateRental($this->getUser());
        }

        $form = $this->createForm(RentalPreferencesType::class, $rental->getPreferences() ?? new Preferences());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Preferences $preferences */
            $preferences = $form->getData();

            $this->rentalService->savePreferences($rental, $preferences);
            return $this->redirectToRouteWithQueryParams('app_create_rental_calendar', $request->query->all());
        }

        return $this->renderForm('create_rental/availabilities.html.twig', [
            'form' => $form,
        ]);
    }


    #[Route('/calendrier', name: 'app_create_rental_calendar')]
    public function calendar(Request $request, ?Rental $rental): Response
    {
        if (null === $rental) {
            $rental = $this->rentalService->findOrCreateRental($this->getUser());
        }

        $form = $this->createForm(RentalUnavailabilitiesType::class, $rental);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->rentalService->saveUnavailabilities($rental, $rental->getUnavailabilities()->toArray());
            return $this->redirectToRouteWithQueryParams('app_create_rental_taxes', $request->query->all());
        }

        return $this->renderForm('create_rental/calendar.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/taxes', name: 'app_create_rental_taxes')]
    public function taxes(Request $request, ?Rental $rental): Response
    {
        if (null === $rental) {
            $rental = $this->rentalService->findOrCreateRental($this->getUser());
        }

        $form = $this->createForm(RentalTaxType::class, $rental->getTax());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Tax $tax */
            $tax = $form->getData();
            $this->rentalService->saveTax($rental, $tax);
            return $this->redirectToRouteWithQueryParams('app_create_rental_prices', $request->query->all());
        }

        return $this->renderForm('create_rental/taxes.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/tarifs', name: 'app_create_rental_prices')]
    public function prices(Request $request, ?Rental $rental): Response
    {
        if (null === $rental) {
            $rental = $this->rentalService->findOrCreateRental($this->getUser());
        }

        $form = $this->createForm(RentalPricesType::class, $rental);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Rental $rental */
            $rental = $form->getData();
            $rental->savePrices($rental->getPrices());
            $this->rentalService->saveEntity($rental);
            return $this->redirectToRouteWithQueryParams('app_create_rental_conditions', $request->query->all());
        }

        return $this->renderForm('create_rental/prices.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/conditions', name: 'app_create_rental_conditions')]
    public function conditions(Request $request, ?Rental $rental): Response
    {
        if (null === $rental) {
            $rental = $this->rentalService->findOrCreateRental($this->getUser());
        }

        $form = $this->createForm(RentalConditionsType::class, $rental->getCondition());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Condition $condition */
            $condition = $form->getData();
            $this->rentalService->saveConditions($rental, $condition);
            return $this->redirectToRouteWithQueryParams('app_rental_created', $request->query->all());
        }

        return $this->renderForm('create_rental/conditions.html.twig', [
            'form' => $form,
        ]);
    }
}
