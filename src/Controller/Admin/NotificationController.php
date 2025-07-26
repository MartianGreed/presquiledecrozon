<?php

namespace App\Controller\Admin;

use App\Entity\Notification;
use App\Infrastructure\Admin\Field\NotificationLabelField;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @extends AbstractCrudController<Notification>
 */
final class NotificationController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $urlGenerator,
    )
    {
    }

    public static function getEntityFqcn(): string
    {
        return Notification::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $markAsReadAction = Action::new('markAsRead', 'Marquer comme vu', 'fas fa-check')
            ->linkToCrudAction('markAsRead')
        ;

        return $actions
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_INDEX, $markAsReadAction)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            NotificationLabelField::new('label'),
            Field::new('createdAt'),
        ];
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $queryBuilder->where($queryBuilder->expr()->isNull('entity.readAt'));

        return $queryBuilder;
    }

    public function markAsRead(AdminContext $context): RedirectResponse
    {
        $notification = $context->getEntity()->getInstance();
        if (!$notification instanceof \App\Entity\Notification) {
            throw new \RuntimeException('Expected Notification entity');
        }

        $notification->markAsRead(new \DateTime());

        $this->entityManager->flush();
        return $this->redirect($this->urlGenerator->setController(__CLASS__)->setAction(Action::INDEX)->generateUrl());
    }
}