<?php

namespace App\Repository\Data;

use App\Entity\Data\Linens;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Linens|null find($id, $lockMode = null, $lockVersion = null)
 * @method Linens|null findOneBy(array $criteria, array $orderBy = null)
 * @method Linens[]    findAll()
 * @method Linens[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinensRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Linens::class);
    }

    // /**
    //  * @return Linens[] Returns an array of Linens objects
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
    public function findOneBySomeField($value): ?Linens
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
