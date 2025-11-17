<?php

namespace App\Repository\Behavior;

use App\Entity\Behavior\UserLimitation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserLimitation|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserLimitation|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserLimitation[]    findAll()
 * @method UserLimitation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserLimitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserLimitation::class);
    }

    /**
     * Get active limitations for a user
     */
    public function findActiveLimitations($user): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.user = :user')
            ->andWhere('l.isActive = :active')
            ->andWhere('l.expiresAt IS NULL OR l.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('active', true)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }
}
