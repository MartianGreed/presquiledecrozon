<?php

namespace App\Controller\Admin;

use App\Entity\Data\Bed;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Bed>
 */
class BedCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Bed::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('label');
        yield TextField::new('help');
        yield ArrayField::new('size');
    }
}
