<?php

namespace App\Repository\Subscription;

use App\Domain\Exception\DefaultSubscriptionNotFound;
use App\Domain\Subscription\Repository\SubscriptionRepository as SubscriptionRepositoryInterface;
use App\Entity\Subscription\Subscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Subscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method Subscription|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Subscription[]    findAll()
 * @method Subscription[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Subscription>
 */
class SubscriptionRepository extends ServiceEntityRepository implements SubscriptionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    final public function findDefaultSubscription(): Subscription
    {
        $qb = $this->createQueryBuilder('s');

        $res = $qb->select('s')
            ->where($qb->expr()->eq('s.name', ':defaultSubscription'))
            ->setParameter('defaultSubscription', 'default')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult()
        ;

        if (null === $res) {
            throw new DefaultSubscriptionNotFound();
        }

        assert($res instanceof Subscription);

        return $res;
    }
}
