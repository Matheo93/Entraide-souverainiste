<?php

namespace App\Repository\Announces;

use App\Entity\User;
use App\Entity\Announces\AnnouncesRequests;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AnnouncesRequests|null find($id, $lockMode = null, $lockVersion = null)
 * @method AnnouncesRequests|null findOneBy(array $criteria, array $orderBy = null)
 * @method AnnouncesRequests[]    findAll()
 * @method AnnouncesRequests[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnnouncesRequestsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnnouncesRequests::class);
    }

    // /**
    //  * @return AnnouncesRequests[] Returns an array of AnnouncesRequests objects
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
    public function findOneBySomeField($value): ?AnnouncesRequests
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */


    public function findAllRequestsByUser(User $user){
        $userId = $user->getId();
        $qb = $this->createQueryBuilder('ar')
        ->innerJoin('ar.announce', 'a')
        ->andWhere('a.user = :userId')
        ->setParameter('userId', $userId)
        ->orderBy('ar.id', 'DESC');


        $results = $qb->getQuery()
        ->getResult();

        return $results;
    }
}
