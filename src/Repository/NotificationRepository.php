<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Notification>
 */
final class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function getUnreadNotifications(): array
    {
        $qb = $this->createQueryBuilder('n');

        return $qb->where($qb->expr()->isNull('n.readAt'))
                  ->getQuery()
                  ->getResult()
        ;
    }

    public function countUnreadNotifications(): int
    {
        $qb = $this->createQueryBuilder('n');

        return $qb
            ->select('count(n.id)')
            ->where($qb->expr()->isNull('n.readAt'))
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}