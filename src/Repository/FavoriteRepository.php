<?php

namespace App\Repository;

use App\Entity\Favorite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @method Favorite|null find($id, $lockMode = null, $lockVersion = null)
 * @method Favorite|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Favorite[]    findAll()
 * @method Favorite[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Favorite>
 */
class FavoriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Favorite::class);
    }

    /** @return array<Favorite> */
    public function getUserFavoritesList(string $personaId): array
    {
        $qb = $this->createQueryBuilder('f');

        /** @var array<Favorite> $favorites */
        $favorites = $qb
            ->select('f', 'r')
            ->innerJoin('f.rental', 'r')
            ->where($qb->expr()->eq('f.persona', ':personaId'))
            ->setParameter('personaId', $personaId)
            ->getQuery()
            ->getResult()
        ;

        return $favorites;
    }

    public function isRentalInUserFavoriteList(string $rentalId, string $personaId): bool
    {
        $qb = $this->createQueryBuilder('f');

        /** @var ?Favorite $favorite */
        $favorite = $qb
            ->where($qb->expr()->eq('f.persona', ':personaId'))
            ->andWhere($qb->expr()->eq('f.rental', ':rentalId'))
            ->setParameters([
                'personaId' => $personaId,
                'rentalId' => $rentalId,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return null !== $favorite;
    }

    public function addNewFavoriteToList(string $rentalId, string $personaId): bool
    {
        $connection = $this->getEntityManager()->getConnection();
        $qb = $connection->createQueryBuilder();
        $now = (new \DateTime())->format('Y-m-d H:i:s');

        $qb->insert($this->getClassMetadata()->getTableName())
            ->values([
                'id' => '?',
                'rental_id' => '?',
                'persona_id' => '?',
                'created_at' => '?',
                'updated_at' => '?',
            ])
            ->setParameters([
                0 => Uuid::v4(),
                1 => $rentalId,
                2 => $personaId,
                3 => $now,
                4 => $now,
            ])
        ;

        try {
            $qb->executeStatement();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function removeFromFavoriteList(string $rentalId, string $personaId): bool
    {
        $connection = $this->getEntityManager()->getConnection();
        $qb = $connection->createQueryBuilder();

        $qb->delete($this->getClassMetadata()->getTableName())
           ->where($qb->expr()->eq('rental_id', ':rentalId'))
            ->andWhere($qb->expr()->eq('persona_id', ':personaId'))
           ->setParameters([
               'rentalId' => $rentalId,
               'personaId' => $personaId,
           ])
        ;

        try {
            $qb->executeStatement();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
