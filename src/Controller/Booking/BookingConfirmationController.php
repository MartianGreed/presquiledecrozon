<?php

namespace App\Controller\Booking;

use App\Domain\Booking\BookingPriceSimulatorService;
use App\Domain\Booking\BookingService;
use App\Domain\Booking\Status;
use App\Domain\Booking\ViewModel\Confirmation;
use App\Entity\Booking\Booking;
use App\Form\Booking\ConfirmBookingType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class BookingConfirmationController extends AbstractController
{
    public function __construct(
        private readonly BookingPriceSimulatorService $simulatorService,
        private readonly BookingService $bookingService,
        private readonly EntityManagerInterface $manager,
    )
    {
    }

    #[Route('/reservation/{id}/confirmation', name: 'app_confirm_booking')]
    #[ParamConverter('booking', Booking::class)]
    public function __invoke(Request $request, Booking $booking): Response
    {
        if (Status::INITIALISED !== $booking->getStatus()) {
            throw $this->createNotFoundException('Booking not found');
        }

        $booking = $this->simulatorService->aggregatePrices($booking);
        $confirmation = new Confirmation($booking);

        $form = $this->createForm(ConfirmBookingType::class, null, [
            'peopleCount' => $booking->getPeopleCount(),
            'ownerMessage' => $confirmation->getDefaultMessageForOwner(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$this->bookingService->isRentalAvailableForBooking($booking->getRental(), $booking)) {
                $form->addError(new FormError('Ce logement semble ne pas etre disponible durant cette période.'));
                return $this->renderForm('booking/confirmation.html.twig', [
                    'vm' => $confirmation,
                    'form' => $form,
                ]);
            }

            $booking->confirm(intval($form->get('peopleCount')->getData()));
            // Create conversation between two users.

            // Save data and redirect to conversation.
            $this->manager->flush();

            return $this->redirectToRoute('app_profile_conversation');
        }

        return $this->renderForm('booking/confirmation.html.twig', [
            'vm' => $confirmation,
            'form' => $form,
        ]);
    }
}