<?php

namespace App\Repository\Booking;

use App\Domain\Booking\Status;
use App\Entity\Booking\Booking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Booking|null find($id, $lockMode = null, $lockVersion = null)
 * @method Booking|null findOneBy(array $criteria, array $orderBy = null)
 * @method Booking[]    findAll()
 * @method Booking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Booking>
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    /** @return Booking[] */
    public function findBookingsForPeriod(string $rentalId, string $bookingId, string $startAt, string $endAt): array
    {
        $qb = $this->createQueryBuilder('b');

        /** @var Booking[] $bookings */
        $bookings = $qb
            ->where($qb->expr()->eq('b.rental', ':rentalId'))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->between('b.startAt', ':from', ':to'),
                    $qb->expr()->between('b.endAt', ':from', ':to')
                )
            )
            ->andWhere($qb->expr()->neq('b.id', ':bookingId'))
            ->setParameters([
                'rentalId' => $rentalId,
                'bookingId' => $bookingId,
                'from' => $startAt,
                'to' => $endAt,
            ])
            ->getQuery()
            ->getResult()
        ;

        return $bookings;
    }

    /** @return array<array{start: string, end: string}> */
    public function getBookingRanges(string $rentalId): array
    {
        $qb = $this->createQueryBuilder('b');
        /** @var array<array{start_at: \DateTimeInterface, end_at: \DateTimeInterface}> $ranges */
        $ranges = $qb
            ->select('b.startAt as start_at', 'b.endAt as end_at')
            ->where($qb->expr()->eq('b.rental', ':rentalId'))
            ->setParameter('rentalId', $rentalId)
            ->getQuery()
            ->getArrayResult()
        ;

        return array_map(static fn ($range) => ['start' => $range['start_at']->format('d/m/Y'), 'end' => $range['end_at']->format('d/m/Y')], $ranges);
    }

    /** @return array<Booking> */
    public function getUserBookings(string $userId): array
    {
        $qb = $this->getUserBookingsQueryBuilder($userId);

        /** @var array<Booking> $bookings */
        $bookings = $qb->getQuery()->getResult();

        return $bookings;
    }

    /** @return array<Booking> */
    public function getUserPastBookings(string $userId): array
    {
        $qb = $this->getUserBookingsQueryBuilder($userId);

        /** @var array<Booking> $bookings */
        $bookings = $qb
            ->andWhere($qb->expr()->lt('b.endAt', ':now'))
            ->setParameter('now', (new \DateTime())->format('Y-m-d'))
            ->getQuery()
            ->getResult()
        ;

        return $bookings;
    }

    /** @return array<Booking> */
    public function getUserForthcomingBookings(string $userId): array
    {
        $now = (new \DateTime())->format('Y-m-d');
        $qb = $this->getUserBookingsQueryBuilder($userId);

        /** @var array<Booking> $bookings */
        $bookings = $qb
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->gte('b.startAt', ':now')),
                    $qb->expr()->gte('b.endAt', ':then')
                )
            ->setParameter('now', $now)
            ->setParameter('then', $now)
            ->getQuery()
            ->getResult()
        ;

        return $bookings;
    }

    /** @return array<Booking> */
    public function getOwnerForthcomingBookings(string $ownerId): array
    {
        $qb = $this->getOwnerBookingsQueryBuilder($ownerId);

        /** @var array<Booking> $bookings */
        $bookings = $qb
            ->andWhere($qb->expr()->eq('b.status', ':bookedStatus'))
            ->setParameter('bookedStatus', Status::BOOKED->value)
            ->getQuery()
            ->getResult()
        ;

        return $bookings;
    }

    /** @return array<Booking> */
    public function getOwnerBookingsToValidate(string $ownerId): array
    {
        $qb = $this->getOwnerBookingsQueryBuilder($ownerId);

        /** @var array<Booking> $bookings */
        $bookings = $qb
            ->andWhere($qb->expr()->eq('b.status', ':toConfirmStatus'))
            ->setParameter('toConfirmStatus', Status::CONFIRMED->value)
            ->getQuery()
            ->getResult()
        ;

        return $bookings;
    }

    /** @return array<Booking> */
    public function getOwnerBookingsHistory(string $ownerId): array
    {
        $qb = $this->getOwnerBookingsQueryBuilder($ownerId);

        /** @var array<Booking> $bookings */
        $bookings = $qb
            ->andWhere($qb->expr()->lt('b.endAt', ':now'))
            ->setParameter('now', (new \DateTime())->format('Y-m-d'))
            ->getQuery()
            ->getResult()
        ;

        return $bookings;
    }

    private function getUserBookingsQueryBuilder(string $userId): QueryBuilder
    {
        $qb = $this->createQueryBuilder('b');

        return $qb
            ->select('b', 'r', 'o', 'p')
            ->join('b.rental', 'r')
            ->join('r.owner', 'o')
            ->join('o.profile', 'p')
            ->where($qb->expr()->eq('b.booker', ':userId'))
            ->addOrderBy('b.startAt')
            ->setParameter('userId', $userId)
        ;
    }

    private function getOwnerBookingsQueryBuilder(string $ownerId): QueryBuilder
    {
        $qb = $this->createQueryBuilder('b');

        return $qb
            ->select('b', 'r', 'o', 'p')
            ->join('b.rental', 'r')
            ->join('b.booker', 'o')
            ->join('o.profile', 'p')
            ->where($qb->expr()->eq('r.owner', ':ownerId'))
            ->addOrderBy('b.startAt')
            ->setParameter('ownerId', $ownerId)
        ;
    }

    public function isBookingAvailableForPeriod(string $rentalId, \DateTimeInterface $startAt, \DateTimeInterface $endAt): bool
    {
        $qb = $this->createQueryBuilder('b');

        $available = $qb
            ->select('count(b.id)')
            ->where($qb->expr()->eq('b.rental', ':rentalId'))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->between('b.startAt', ':from', ':to'),
                    $qb->expr()->between('b.endAt', ':from', ':to'),
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

        return 0 === $available;
    }
}
