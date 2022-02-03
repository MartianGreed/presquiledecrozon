<?php

namespace App\Controller\Admin;

use App\Entity\Data\RentalType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class RentalTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RentalType::class;
    }
}
