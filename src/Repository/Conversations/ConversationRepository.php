<?php

namespace App\Repository\Conversations;

use App\Entity\Conversations\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Conversation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conversation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conversation[]    findAll()
 * @method Conversation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    /**
     * Find conversations older than specified days with specific statuses
     * Used for auto-close CRON job
     */
    public function findOldConversations(int $daysOld = 7): array
    {
        $date = new \DateTime("-{$daysOld} days");

        return $this->createQueryBuilder('c')
            ->where('c.status IN (:statuses)')
            ->andWhere('c.lastMessageAt < :timeout')
            ->setParameter('statuses', ['EN_ATTENTE_REPONSE', 'EN_COURS', 'ACTIF'])
            ->setParameter('timeout', $date)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find active conversation between two users for specific announce
     */
    public function findActiveConversation($announce, $userOffrant, $userDemandeur): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->where('c.announce = :announce')
            ->andWhere('c.userOffrant = :offrant')
            ->andWhere('c.userDemandeur = :demandeur')
            ->andWhere('c.status NOT IN (:closedStatuses)')
            ->setParameter('announce', $announce)
            ->setParameter('offrant', $userOffrant)
            ->setParameter('demandeur', $userDemandeur)
            ->setParameter('closedStatuses', ['CLOTURE_ACCORD', 'CLOTURE_DESACCORD', 'CLOTURE_AUTO'])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get all conversations for a user (as offrant or demandeur)
     */
    public function findUserConversations($user): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.userOffrant = :user OR c.userDemandeur = :user')
            ->setParameter('user', $user)
            ->orderBy('c.lastMessageAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
