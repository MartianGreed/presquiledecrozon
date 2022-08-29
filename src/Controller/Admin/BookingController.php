<?php

namespace App\Controller\Admin;

use App\Entity\Booking\Booking;
use App\Infrastructure\Admin\Field\BookingStatusField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;

final class BookingController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Booking::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            EmailField::new('rental.owner.email', 'Propriétaire'),
            EmailField::new('booker.email', 'Locataire'),
            DateField::new('startAt', 'Du'),
            DateField::new('endAt', 'Au'),
            BookingStatusField::new('status'),
        ];
    }
}