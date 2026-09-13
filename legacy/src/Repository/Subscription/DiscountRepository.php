<?php

namespace App\Repository\Subscription;

use App\Entity\Subscription\Discount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Discount|null find($id, $lockMode = null, $lockVersion = null)
 * @method Discount|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Discount[]    findAll()
 * @method Discount[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Discount>
 */
class DiscountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Discount::class);
    }
}
