<?php

namespace App\Controller\Admin;

use App\Entity\Booking\Booking;
use App\Entity\Data\Bed;
use App\Entity\Data\Furniture;
use App\Entity\Data\RentalType;
use App\Entity\Data\Town;
use App\Entity\Notification;
use App\Entity\Rental\Rental;
use App\Entity\Subscription\Discount;
use App\Entity\Subscription\Subscription;
use App\Entity\User;
use App\Repository\NotificationRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(private readonly NotificationRepository $notificationRepository)
    {
    }

    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Presqu\'île de Crozon')
            ->renderContentMaximized()
        ;
    }

    public function configureMenuItems(): iterable
    {
        $unreadNotificationsCount = $this->notificationRepository->countUnreadNotifications();

        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section();
        $notificationMessage = 0 === $unreadNotificationsCount ? 'Notifications' : sprintf('Notifications <span class="badge bg-danger text-white">%d</span>', $unreadNotificationsCount);
        yield MenuItem::linkToCrud($notificationMessage, 'fas fa-bell', Notification::class);

        yield MenuItem::section('Contenu');
        yield MenuItem::linkToCrud('Type de locations', 'fas fa-home', RentalType::class);
        yield MenuItem::linkToCrud('Type de lits', 'fas fa-bed', Bed::class);
        yield MenuItem::linkToCrud('Equipements', 'fas fa-tools', Furniture::class);
        yield MenuItem::linkToCrud('Villes', 'fas fa-hotel', Town::class);

        yield MenuItem::section('Abonnements');
        yield MenuItem::linkToCrud('Abonnement', 'fas fa-wallet', Subscription::class);
        yield MenuItem::linkToCrud('Code promo', 'far fa-money-bill-alt', Discount::class);

        yield MenuItem::section();
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-user', User::class);

        yield MenuItem::section('Annonces & Réservations');
        yield MenuItem::linkToCrud('Annonces', 'fas fa-hospital-user', Rental::class);
        yield MenuItem::linkToCrud('Réservations', 'fas fa-money-check-dollar', Booking::class);
    }
}
