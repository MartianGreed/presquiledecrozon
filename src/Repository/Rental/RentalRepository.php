<?php

namespace App\Repository\Rental;

use App\Domain\Exception\EntityNotFoundException;
use App\Domain\Exception\RentalNotFoundException;
use App\Domain\Rental\Status;
use App\Entity\Rental\Rental;
use App\Entity\Rental\Unavailability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    private function getBaseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('r');
    }

    /**
     * @throws EntityNotFoundException|\Doctrine\ORM\NonUniqueResultException
     */
    final public function findLatestDraftRentalForUser(string $userId): Rental
    {
        $qb = $this->getBaseQueryBuilder();

        /** @var ?Rental $rental */
        $rental = $qb
            ->select('r', 'p')
            ->leftJoin('r.preferences', 'p')
            ->where($qb->expr()->neq('r.status', ':published'))
            ->andWhere($qb->expr()->eq('r.owner', ':userId'))
            ->setParameters([
                'published' => Status::PUBLISHED->value,
                'userId' => $userId,
            ])
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

    /** @return array<Rental> */
    final public function findUserRentals(string $userId): array
    {
        $qb = $this->getBaseQueryBuilder();

        /** @var array<Rental> $rentals */
        $rentals = $qb
            ->where($qb->expr()->eq('r.owner', "'$userId'"))
            ->andWhere($qb->expr()->neq('r.status', "'" . Status::DRAFT->value . "'"))
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        return $rentals;
    }

    // TODO: Improve logic here to get more pertinent results.
    /** @return array<Rental> */
    public function findFeatured(int $max = 3): array
    {
        $qb = $this->getBaseQueryBuilder();

        /** @var array<Rental> $rentals */
        $rentals = $qb
            ->andWhere($qb->expr()->eq('r.status', ':published'))
            ->orderBy('r.createdAt', 'DESC')
            ->setParameter('published', Status::PUBLISHED->value)
            ->setMaxResults($max)
            ->getQuery()
            ->getResult()
        ;

        return $rentals;
    }

    public function getPaginatedList(): QueryBuilder
    {
        return $this->getBaseQueryBuilder()->orderBy('r.createdAt', 'DESC');
    }


    public function fetchRentalDetails(string $slug): Rental
    {
        $qb = $this->getBaseQueryBuilder();

        /** @var ?Rental $rental */
        $rental = $qb
            ->select('r', 'd')
            ->join('r.description', 'd')
            ->where($qb->expr()->eq('r.slug', "'" . $slug . "'"))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (null === $rental) {
            throw new RentalNotFoundException($slug);
        }

        return $rental;
    }

    public function userHasRental(string $userId): bool
    {
        $qb = $this->getBaseQueryBuilder();

        /** @var int $rentalCount */
        $rentalCount = $qb
            ->select('count(r)')
            ->where($qb->expr()->eq('r.status', ':published'))
            ->andWhere($qb->expr()->eq('r.owner', ':userId'))
            ->setParameters([
                'published' => Status::PUBLISHED->value,
                'userId' => $userId,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return 0 < $rentalCount;
    }

    public function hasUnavailabilitiesForPeriod(string $rentalId, \DateTimeInterface $startAt, \DateTimeInterface $endAt): bool
    {
        $qb = $this->getBaseQueryBuilder();

        $unavailabilites = $qb
            ->select('count(u.id)')
            ->leftJoin('r.unavailabilities', 'u')
            ->where($qb->expr()->eq('r.id', ':rentalId'))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->between('u.startAt', ':from', ':to'),
                    $qb->expr()->between('u.endAt', ':from', ':to'),
                )
            )
            ->setParameters([
                'rentalId' => $rentalId,
                'from' => $startAt->format('Y-m-d'),
                'to' => $endAt->format('Y-m-d'),
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return 0 < $unavailabilites;
    }
}
