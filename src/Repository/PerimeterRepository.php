<?php

namespace App\Repository;

use App\Entity\Perimeter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Perimeter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Perimeter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Perimeter[]    findAll()
 * @method Perimeter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PerimeterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Perimeter::class);
    }

    public function findByDeleted($startDate, $endDate, $site)
    {
        return $this->createQueryBuilder('c')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->andwhere('c.deletedAt IS NOT NULL')
            ->andwhere('c.deletedAt > :startDate')
            ->andwhere('c.deletedAt < :endDate')
            ->orderBy('c.deletedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
