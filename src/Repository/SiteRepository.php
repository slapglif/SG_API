<?php

namespace App\Repository;

use App\Entity\Site;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Site|null find($id, $lockMode = null, $lockVersion = null)
 * @method Site|null findOneBy(array $criteria, array $orderBy = null)
 * @method Site[]    findAll()
 * @method Site[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Site::class);
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
