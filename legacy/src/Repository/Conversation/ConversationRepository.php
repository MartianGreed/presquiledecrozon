<?php

namespace App\Repository\Conversation;

use App\Domain\Exception\ConversationNotFoundException;
use App\Entity\Conversation\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Conversation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conversation|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Conversation[]    findAll()
 * @method Conversation[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    /**
     * @return array<Conversation>
     */
    public function getUserConversations(?string $userId): array
    {
        $qb = $this->createQueryBuilder('c');

        /** @var array<Conversation> $res */
        $res = $qb
            ->select('c', 's', 'r', 'sp', 'rp')
            ->join('c.sender', 's')
            ->join('c.receiver', 'r')
            ->join('s.profile', 'sp')
            ->join('r.profile', 'rp')
            ->where($qb->expr()->orX(
                $qb->expr()->eq('c.sender', ':userId'),
                $qb->expr()->eq('c.receiver', ':userId')
            ))
            ->orderBy('c.updatedAt', 'DESC')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();

        return $res;
    }

    public function getConversationDetails(string $id): Conversation
    {
        $qb = $this->createQueryBuilder('c');

        /** @var ?Conversation $conversation */
        $conversation = $qb
            ->select('c', 's', 'r', 'sp', 'rp', 'b')
            ->join('c.sender', 's')
            ->join('c.receiver', 'r')
            ->join('s.profile', 'sp')
            ->join('r.profile', 'rp')
            ->join('c.booking', 'b')
            ->where($qb->expr()->eq('c.id', ':id'))
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $conversation) {
            throw new ConversationNotFoundException($id);
        }

        return $conversation;
    }

    public function exists(string $conversationId): bool
    {
        $qb = $this->createQueryBuilder('c');

        return null !== $qb->where($qb->expr()->eq('c.id', ':conversationId'))
            ->setParameter('conversationId', $conversationId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
