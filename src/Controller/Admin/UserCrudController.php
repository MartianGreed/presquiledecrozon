<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield EmailField::new('email');

        yield ChoiceField::new('profile.gender')
            ->setChoices(['M' => 'M', 'Mme' => 'F'])
            ->renderExpanded()
            ->allowMultipleChoices(false)
            ->setRequired(true)
            ->setColumns(2)
        ;
        yield TextField::new('profile.firstname')->setColumns(5);
        yield TextField::new('profile.lastname')->setColumns(5);
        yield DateField::new('profile.birthdate')->setColumns(2);
        yield TextField::new('profile.cellphone')->setColumns(6);
        yield DateTimeField::new('createdAt');
    }
}
