<?php

namespace App\Repository\Data;

use App\Entity\Data\Furniture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Furniture|null find($id, $lockMode = null, $lockVersion = null)
 * @method Furniture|null findOneBy(array $criteria, array $orderBy = null)
 * @method Furniture[]    findAll()
 * @method Furniture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Furniture>
 */
class FurnitureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Furniture::class);
    }
}
