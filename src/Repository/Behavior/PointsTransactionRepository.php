<?php

namespace App\Repository\Behavior;

use App\Entity\Behavior\PointsTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PointsTransaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method PointsTransaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method PointsTransaction[]    findAll()
 * @method PointsTransaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PointsTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PointsTransaction::class);
    }

    /**
     * Get transaction history for a user
     */
    public function findUserTransactions($user): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.user = :user')
            ->setParameter('user', $user)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
