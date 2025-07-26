<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Notification>
 */
final class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /** @return array<Notification> */
    public function getUnreadNotifications(): array
    {
        $qb = $this->createQueryBuilder('n');

        /** @var array<Notification> */
        $notifications = $qb->where($qb->expr()->isNull('n.readAt'))
                            ->getQuery()
                            ->getResult()
        ;
        return $notifications;
    }

    public function countUnreadNotifications(): int
    {
        $qb = $this->createQueryBuilder('n');

        return intval($qb
            ->select('count(n.id)')
            ->where($qb->expr()->isNull('n.readAt'))
            ->getQuery()
            ->getSingleScalarResult())
        ;
    }
}