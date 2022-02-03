<?php

namespace App\Controller\Admin;

use App\Entity\Subscription\Subscription;
use App\Infrastructure\Admin\Field\PriceField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SubscriptionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Subscription::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('validityDuration')->onlyOnForms(),
            TextField::new('name'),
            PriceField::new('amount'),
        ];
    }
}
