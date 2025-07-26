<?php

namespace App\Controller\Admin;

use App\Domain\Rental\RentalPreferences;
use App\Entity\Rental\Rental;
use App\Form\Types\MediaType;
use App\Form\Types\PricesType;
use App\Form\Types\UnavailabilityType;
use App\Infrastructure\Admin\Field\MediaField;
use App\Infrastructure\Admin\Field\PriceField;
use App\Infrastructure\Admin\Field\RentalStatusField;
use App\Infrastructure\Admin\Form\AdminBedroomType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Rental>
 */
class RentalCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Rental::class;
    }

    public function configureFields(string $pageName): iterable
    {
        if ($pageName === Crud::PAGE_INDEX) {
            yield IdField::new('id')->onlyOnIndex();
            yield TextField::new('description.title', 'Titre');
            yield TextField::new('configuration.type.label', 'Type');
            yield TextField::new('address.town.name', 'Commune');
            yield RentalStatusField::new('status');
            return;
        }

        yield FormField::addTab('Configuration');
        yield FormField::addPanel('Configuration');
        yield AssociationField::new('configuration.type')
                    ->setCrudController(RentalTypeCrudController::class)
                ;
        yield NumberField::new('configuration.peopleCount');
        yield CollectionField::new('configuration.bedrooms')
                    ->setEntryType(AdminBedroomType::class)
                    ->setEntryIsComplex(true)
                ;

        yield FormField::addPanel('Equipements');
        yield AssociationField::new('furnitures')->setCrudController(FurnitureCrudController::class);
        yield CollectionField::new('customFurnitures');

        yield FormField::addPanel('Description');
        yield TextField::new('description.title')->setColumns(6);
        yield SlugField::new('slug')->setTargetFieldName('description_title')->setColumns(6);
        yield TextareaField::new('description.description')->setColumns(12);

        yield FormField::addPanel('Addresse');
        yield TextField::new('address.address')->setColumns(6);
        yield TextField::new('address.address2')->setColumns(6);
        yield AssociationField::new('address.town')
                    ->setCrudController(TownCrudController::class)
                    ->setRequired(true)
                ;

        yield FormField::addTab('Photos');
        yield MediaField::new('gallery.cover');
        yield CollectionField::new('gallery.pictures')
                ->setEntryType(MediaType::class)
                ->setEntryIsComplex(true)
            ;

        yield FormField::addTab('Disponibilités');
        yield FormField::addPanel('Préférences');
        yield ChoiceField::new('preferences.acceptedLastBooking')
                    ->setColumns(6)
                    ->setChoices(RentalPreferences::acceptedLastBookingChoices())
                    ->setRequired(true)
                ;
        yield ChoiceField::new('preferences.maxTimeBeforeBooking')
                    ->setColumns(6)
                    ->setChoices(RentalPreferences::maxTimeBeforeBookingChoices())
                    ->setRequired(true)
                ;
        yield ChoiceField::new('preferences.beginBookingAt')
                    ->setColumns(6)
                    ->setChoices(RentalPreferences::beginBookingAt())
                    ->setRequired(true)
                ;
        yield ChoiceField::new('preferences.endBookingAt')
                    ->setColumns(6)
                    ->setChoices(RentalPreferences::endBookingAt())
                    ->setRequired(true)
                ;

        yield FormField::addPanel('Périodes d\'indisponibilité');
        yield CollectionField::new('unavailabilities')
                    ->setEntryType(UnavailabilityType::class)
                    ->setEntryIsComplex(true)
                ;

        yield FormField::addTab('Tarifs');
        yield PriceField::new('weeklyRate', 'Tarif de base à la semaine')->setColumns(6);
        yield PriceField::new('dailyRate', 'Tarif de base à la nuitée')->setColumns(6);

        yield FormField::addPanel('Taxes');
        yield TextField::new('tax.localTax');
        yield PriceField::new('tax.cleaningTax');
        yield PriceField::new('tax.linensTax');
//                yield AssociationField::new('tax.linens')
//                    ->setCrudController(LinensCrudController::class)
//                ;
        yield FormField::addPanel('Tranches tarifaires');
        yield CollectionField::new('prices')
                    ->setEntryIsComplex(true)
                    ->setEntryType(PricesType::class)
                ;
    }
}
