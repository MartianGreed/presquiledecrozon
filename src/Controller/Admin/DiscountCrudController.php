<?php

namespace App\Controller\Admin;

use App\Entity\Subscription\Discount;
use App\Infrastructure\Admin\Field\PriceField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Discount>
 */
class DiscountCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Discount::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('code'),
            TextField::new('type'),
            PriceField::new('amount'),
            DateField::new('expiresAt'),
            AssociationField::new('payee'),
        ];
    }
}
