<?php

namespace App\Controller\Admin;

use App\Entity\Data\Bed;
use App\Entity\Data\Furniture;
use App\Entity\Data\RentalType;
use App\Entity\Data\Town;
use App\Entity\Rental\Rental;
use App\Entity\Subscription\Discount;
use App\Entity\Subscription\Subscription;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
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
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

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
    }
}
