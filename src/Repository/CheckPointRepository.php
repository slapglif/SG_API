<?php

namespace App\Repository;

use App\Entity\CheckPoint;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CheckPoint|null find($id, $lockMode = null, $lockVersion = null)
 * @method CheckPoint|null findOneBy(array $criteria, array $orderBy = null)
 * @method CheckPoint[]    findAll()
 * @method CheckPoint[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CheckPointRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CheckPoint::class);
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
