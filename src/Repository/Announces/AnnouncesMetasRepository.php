<?php

namespace App\Repository\Announces;

use App\Entity\Announces\AnnouncesMetas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AnnouncesMetas|null find($id, $lockMode = null, $lockVersion = null)
 * @method AnnouncesMetas|null findOneBy(array $criteria, array $orderBy = null)
 * @method AnnouncesMetas[]    findAll()
 * @method AnnouncesMetas[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnnouncesMetasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnnouncesMetas::class);
    }

    // /**
    //  * @return AnnouncesMetas[] Returns an array of AnnouncesMetas objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AnnouncesMetas
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
