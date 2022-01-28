<?php

namespace App\Repository\Rental;

use App\Domain\Exception\EntityNotFoundException;
use App\Domain\Rental\Status;
use App\Entity\Rental\Rental;
use App\Entity\Rental\Unavailability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Rental|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rental|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rental[]    findAll()
 * @method Rental[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Rental>
 */
class RentalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rental::class);
    }

    /**
     * @throws EntityNotFoundException|\Doctrine\ORM\NonUniqueResultException
     */
    final public function findLatestDraftRentalForUser(string $userId): Rental
    {
        $qb = $this->createQueryBuilder('r');

        /** @var ?Rental $rental */
        $rental = $qb
            ->select('r', 'p')
            ->join('r.preferences', 'p')
            ->where($qb->expr()->neq('r.status', "'" . Status::PUBLISHED->value . "'"))
            ->andWhere($qb->expr()->eq('r.owner', "'$userId'"))
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult()
        ;

        if (null === $rental) {
            throw new EntityNotFoundException('No unpublished rental found for user :' . $userId);
        }

        return $rental;
    }
}
