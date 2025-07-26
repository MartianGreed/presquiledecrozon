<?php

namespace App\Repository\Rental;

use App\Entity\Rental\Bedroom;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Bedroom|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bedroom|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Bedroom[]    findAll()
 * @method Bedroom[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Bedroom>
 */
class BedroomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bedroom::class);
    }
}
