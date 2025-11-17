<?php

namespace App\Repository\Conversations;

use App\Entity\Conversations\ConversationMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConversationMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConversationMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConversationMessage[]    findAll()
 * @method ConversationMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConversationMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConversationMessage::class);
    }
}
