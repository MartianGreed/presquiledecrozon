<?php

namespace App\Repository\Data;

use App\Entity\Data\LinensCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LinensCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method LinensCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method LinensCategory[]    findAll()
 * @method LinensCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinensCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LinensCategory::class);
    }

    // /**
    //  * @return LinensCategory[] Returns an array of LinensCategory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LinensCategory
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
