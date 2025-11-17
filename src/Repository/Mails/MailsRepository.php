<?php

namespace App\Repository\Mails;

use App\Entity\Mails\Mails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Mails|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mails|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mails[]    findAll()
 * @method Mails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mails::class);
    }

    // /**
    //  * @return Mails[] Returns an array of Mails objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Mails
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
