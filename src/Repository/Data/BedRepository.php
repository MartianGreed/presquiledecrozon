<?php

namespace App\Repository\Data;

use App\Entity\Data\Bed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Bed|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bed|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Bed[]    findAll()
 * @method Bed[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Bed>
 */
class BedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bed::class);
    }
}
