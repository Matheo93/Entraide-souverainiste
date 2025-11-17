<?php

namespace App\Repository\Settings;

use App\Entity\Settings\Cities;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Cities|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cities|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cities[]    findAll()
 * @method Cities[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CitiesRepository extends ServiceEntityRepository
{
	// Obligé de renseigner une entité
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cities::class);
    }

}
