<?php

namespace App\Repository\Rental;

use App\Entity\Rental\Condition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Condition|null find($id, $lockMode = null, $lockVersion = null)
 * @method Condition|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Condition[]    findAll()
 * @method Condition[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Condition>
 */
class ConditionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Condition::class);
    }
}
