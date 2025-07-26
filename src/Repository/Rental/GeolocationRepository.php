<?php

namespace App\Repository\Rental;

use App\Entity\Rental\Geolocation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Geolocation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Geolocation|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Geolocation[]    findAll()
 * @method Geolocation[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Geolocation>
 */
class GeolocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Geolocation::class);
    }
}
