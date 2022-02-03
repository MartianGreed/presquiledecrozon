<?php

namespace App\Controller\Admin;

use App\Entity\Data\Furniture;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class FurnitureCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Furniture::class;
    }
}
