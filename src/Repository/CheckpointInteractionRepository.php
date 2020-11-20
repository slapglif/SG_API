<?php

namespace App\Repository;

use App\Entity\CheckpointInteraction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CheckpointInteraction|null find($id, $lockMode = null, $lockVersion = null)
 * @method CheckpointInteraction|null findOneBy(array $criteria, array $orderBy = null)
 * @method CheckpointInteraction[]    findAll()
 * @method CheckpointInteraction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CheckpointInteractionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CheckpointInteraction::class);
    }

    public function findByDeleted($startDate, $endDate, $site)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.shift', 'gs')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('site', $site)
            ->andwhere('c.deletedAt IS NOT NULL')
            ->andwhere('c.deletedAt > :startDate')
            ->andwhere('c.deletedAt < :endDate')
            ->andwhere('gs.site = :site')
            ->orderBy('c.deletedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
