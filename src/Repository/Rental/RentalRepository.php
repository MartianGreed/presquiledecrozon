<?php

namespace App\Repository\Rental;

use App\Domain\Exception\EntityNotFoundException;
use App\Domain\Exception\RentalNotFoundException;
use App\Domain\Rental\Status;
use App\Entity\Rental\Rental;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Rental|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rental|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Rental[]    findAll()
 * @method Rental[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
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

    /**
     * @return array<Rental>
     */
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
    /**
     * @return array<Rental>
     */
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
        $qb = $this->getBaseQueryBuilder();
        $qb = $this->addEntitiesJoin($qb);

        return $qb
            ->andWhere($qb->expr()->eq('r.status', ':published'))
            ->setParameter('published', Status::PUBLISHED->value)
            ->orderBy('r.createdAt', 'DESC')
        ;
    }

    public function fetchRentalDetails(string $slug): Rental
    {
        $qb = $this->getBaseQueryBuilder();
        $qb = $this->addEntitiesJoin($qb);

        /** @var ?Rental $rental */
        $rental = $qb
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

    private function addEntitiesJoin(QueryBuilder $qb): QueryBuilder
    {
        return $qb
            ->select('r', 'd', 'co', 'u', 'g', 'cover', 'p', 's', 'a', 'at', 'pt', 'dep', 'geo', 'rt', 'bed', 'beds', 'ow', 'owp')
            ->leftJoin('r.description', 'd')
            ->leftJoin('r.activeSubscription', 's')
            ->leftJoin('r.configuration', 'co')
            ->leftJoin('co.bedrooms', 'bed')
            ->leftJoin('co.type', 'rt')
            ->leftJoin('bed.beds', 'beds')
            ->leftJoin('r.unavailabilities', 'u')
            ->leftJoin('r.gallery', 'g')
            ->leftJoin('g.cover', 'cover')
//            ->leftJoin('g.pictures', 'pictures')
            ->leftJoin('r.preferences', 'p')
            ->leftJoin('r.address', 'a')
            ->leftJoin('a.town', 'at')
            ->leftJoin('at.postalCode', 'pt')
            ->leftJoin('pt.department', 'dep')
            ->leftJoin('r.geolocation', 'geo')
            ->leftJoin('r.owner', 'ow')
            ->leftJoin('ow.profile', 'owp')
        ;
    }
}
