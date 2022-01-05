<?php

namespace App\Repository\Data;

use App\Entity\Data\PostalCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PostalCode|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostalCode|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostalCode[]    findAll()
 * @method PostalCode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<PostalCode>
 */
class PostalCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostalCode::class);
    }
}
