<?php

namespace App\Repository\Rental;

use App\Entity\Rental\Tax;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tax|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tax|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Tax[]    findAll()
 * @method Tax[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Tax>
 */
class TaxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tax::class);
    }
}
