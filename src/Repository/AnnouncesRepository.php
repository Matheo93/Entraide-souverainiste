<?php

namespace App\Repository;

use App\Entity\Announces\Announces;
use App\Entity\Categories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;

use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Announces|null find($id, $lockMode = null, $lockVersion = null)
 * @method Announces|null findOneBy(array $criteria, array $orderBy = null)
 * @method Announces[]    findAll()
 * @method Announces[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnnouncesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Announces::class);
    }

    // /**
    //  * @return Announces[] Returns an array of Announces objects
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
    public function findOneBySomeField($value): ?Announces
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */


    public function getByTerms(string $term, Categories|null $category, bool $isRemote){
        $qb = $this->createQueryBuilder('a');

        if($term) $qb
        ->andWhere('a.title LIKE :term')
        ->setParameter('term', "%" . $term . "%");
        if($isRemote)
        $qb->andWhere('a.isRemote = :isRemote')
        ->setParameter('isRemote', $isRemote);

        if($category){
            $qb->innerJoin('a.categories', 'ac')
            ->andWhere("ac.id = :categoryId")
            ->setParameter('categoryId', $category->getId());
        }

        $qb->orderBy('a.dateAdded', 'DESC')
        ->andWhere('a.isActive = 1');

        $results = $qb
        ->getQuery()
        ->getResult();

        return $results;
    ;
    }
}
