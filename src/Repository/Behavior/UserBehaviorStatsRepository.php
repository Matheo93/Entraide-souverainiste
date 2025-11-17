<?php

namespace App\Repository\Behavior;

use App\Entity\Behavior\UserBehaviorStats;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserBehaviorStats|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserBehaviorStats|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserBehaviorStats[]    findAll()
 * @method UserBehaviorStats[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserBehaviorStatsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBehaviorStats::class);
    }

    /**
     * Find users flagged as profiteurs
     */
    public function findProfiteurs(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.profiteurLevel = :level')
            ->setParameter('level', 'PROFITEUR')
            ->orderBy('s.profiteurScore', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find suspect users (moderate profiteur score)
     */
    public function findSuspects(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.profiteurLevel = :level')
            ->setParameter('level', 'SUSPECT')
            ->orderBy('s.profiteurScore', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
