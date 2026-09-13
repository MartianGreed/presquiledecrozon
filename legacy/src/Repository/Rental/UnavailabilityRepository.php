<?php

namespace App\Repository\Rental;

use App\Entity\Rental\Unavailability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Unavailability|null find($id, $lockMode = null, $lockVersion = null)
 * @method Unavailability|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Unavailability[]    findAll()
 * @method Unavailability[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Unavailability>
 */
class UnavailabilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Unavailability::class);
    }
}
