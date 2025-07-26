<?php

namespace App\Repository\Subscription;

use App\Domain\Exception\RentalSubscriptionNotFound;
use App\Domain\Subscription\SubscriptionStatus;
use App\Entity\Subscription\RentalSubscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RentalSubscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method RentalSubscription|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method RentalSubscription[]    findAll()
 * @method RentalSubscription[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<RentalSubscription>
 */
class RentalSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RentalSubscription::class);
    }

    final public function save(RentalSubscription $subscription): void
    {
        $this->getEntityManager()->persist($subscription);
        $this->getEntityManager()->flush();
    }

    final public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    /**
     * @throws RentalSubscriptionNotFound
     */
    final public function findSubscriptionForRental(string $rentalId): RentalSubscription
    {
        $qb = $this->createQueryBuilder('rs');

        $res = $qb->select('rs', 'r', 's', 'd')
            ->join('rs.rental', 'r')
            ->join('rs.subscription', 's')
            ->leftJoin('rs.discount', 'd')
            ->where($qb->expr()->eq('rs.rental', "'$rentalId'"))
            ->andWhere($qb->expr()->eq('rs.status', "'" . SubscriptionStatus::DRAFT->value . "'"))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (null === $res) {
            throw new RentalSubscriptionNotFound($rentalId);
        }

        assert($res instanceof RentalSubscription);

        return $res;
    }
}
