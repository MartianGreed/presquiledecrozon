<?php

namespace App\Controller\Rental;

use App\Domain\Booking\BookingValidator;
use App\Domain\Booking\Exception\BookingDoesNotFullfillOwnerPreferencesException;
use App\Domain\Booking\Exception\CannotBookOwnRentalException;
use App\Domain\Booking\Exception\RentalNotAvailableForPeriodException;
use App\Domain\Booking\Exception\TooManyPeopleInBookingException;
use App\Domain\Exception\RentalNotFoundException;
use App\Entity\Booking\Booking;
use App\Entity\Rental\Rental;
use App\Entity\User;
use App\Form\Booking\BookRentalType;
use App\Message\RentalHasBeenBooked;
use App\Repository\Booking\BookingRepository;
use App\Repository\Rental\RentalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

final class RentalDetailController extends AbstractController
{
    public function __construct(
        private readonly RentalRepository $rentalRepository,
        private readonly BookingRepository $bookingRepository,
        private readonly EntityManagerInterface $manager,
        private readonly BookingValidator $bookingValidator,
        private readonly MessageBusInterface $bus,
    ) {
    }

    #[Route('/annonce/{slug}', name: 'app_rental_details')]
    public function __invoke(Request $request, string $slug): Response
    {
        try {
            $rental = $this->rentalRepository->fetchRentalDetails($slug);
        } catch (RentalNotFoundException $e) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(BookRentalType::class, null, [
            'rental' => $rental
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ?User $booker */
            $booker = $this->getUser();
            if (null === $booker) {
                $request->getSession()->set('_security.main.target_path', $request->getRequestUri());
                return $this->redirectToRoute('app_login');
            }

            /** @var \DateTimeInterface $startAt */
            $startAt = $form->get('startAt')->getData();
            /** @var \DateTimeInterface $endAt */
            $endAt = $form->get('endAt')->getData();

            try {
                $booking = Booking::init(
                    $this->bookingValidator,
                    $rental,
                    $booker,
                    $startAt,
                    $endAt,
                    intval($form->get('peopleCount')->getData()),
                );
            } catch (RentalNotAvailableForPeriodException | BookingDoesNotFullfillOwnerPreferencesException | CannotBookOwnRentalException $e) {
                return $this->handleBookingDomainErrors($rental, $form, new FormError($e->getMessage()), 'startAt');
            } catch (TooManyPeopleInBookingException $e) {
                return $this->handleBookingDomainErrors($rental, $form, new FormError($e->getMessage()), 'peopleCount');
            }

            $this->manager->persist($booking);
            $this->manager->flush();

            $this->bus->dispatch(new RentalHasBeenBooked($rental->getId(), $booking->getId(), $booking->getCreatedAt()->format('Y-m-d H:i:s')));

            return $this->redirectToRoute('app_confirm_booking', ['id' => $booking->getId()]);
        }

        return $this->renderForm('page/rental-detail.html.twig', [
            'rental' => $rental,
            'form' => $form,
            'bookings' => $this->bookingRepository->getBookingRanges((string) $rental->getId()),
        ]);
    }

    private function handleBookingDomainErrors(
        Rental $rental,
        FormInterface $form,
        ?FormError $error = null,
        ?string $field = null
    ): Response {
        if (null !== $error && null !== $field) {
            $form->get($field)->addError($error);
        }
        return $this->renderForm('page/rental-detail.html.twig', [
            'rental' => $rental,
            'form' => $form,
            'bookings' => $this->bookingRepository->getBookingRanges((string) $rental->getId()),
        ]);
    }
}
