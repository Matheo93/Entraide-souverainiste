<?php

namespace App\Repository\Security;

use App\Entity\Security\IpBan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IpBan|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpBan|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpBan[]    findAll()
 * @method IpBan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpBanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpBan::class);
    }

    /**
     * Check if IP is currently banned
     */
    public function isIpBanned(string $ip): bool
    {
        $qb = $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->where('b.ipAddress = :ip')
            ->andWhere('b.isActive = true')
            ->andWhere('(b.expiresAt IS NULL OR b.expiresAt > :now)')
            ->setParameter('ip', $ip)
            ->setParameter('now', new \DateTime());

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * Get active ban for IP
     */
    public function getActiveBan(string $ip): ?IpBan
    {
        return $this->createQueryBuilder('b')
            ->where('b.ipAddress = :ip')
            ->andWhere('b.isActive = true')
            ->andWhere('(b.expiresAt IS NULL OR b.expiresAt > :now)')
            ->setParameter('ip', $ip)
            ->setParameter('now', new \DateTime())
            ->orderBy('b.bannedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get all active bans
     */
    public function getActiveBans(): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.isActive = true')
            ->andWhere('(b.expiresAt IS NULL OR b.expiresAt > :now)')
            ->setParameter('now', new \DateTime())
            ->orderBy('b.bannedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Expire old temporary bans
     */
    public function expireOldBans(): int
    {
        return $this->createQueryBuilder('b')
            ->update()
            ->set('b.isActive', 'false')
            ->where('b.isActive = true')
            ->andWhere('b.expiresAt IS NOT NULL')
            ->andWhere('b.expiresAt < :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->execute();
    }
}
