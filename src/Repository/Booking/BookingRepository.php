<?php

namespace App\Repository\Booking;

use App\Entity\Booking\Booking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
