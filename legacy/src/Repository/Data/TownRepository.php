<?php

namespace App\Repository\Data;

use App\Entity\Data\Town;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Town|null find($id, $lockMode = null, $lockVersion = null)
 * @method Town|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Town[]    findAll()
 * @method Town[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Town>
 */
class TownRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Town::class);
    }
}
