<?php

namespace App\Controller\Admin;

use App\Entity\Data\Linens;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class LinensCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Linens::class;
    }
}
