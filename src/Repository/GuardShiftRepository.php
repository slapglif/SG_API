<?php

namespace App\Repository;

use App\Entity\GuardShift;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method GuardShift|null find($id, $lockMode = null, $lockVersion = null)
 * @method GuardShift|null findOneBy(array $criteria, array $orderBy = null)
 * @method GuardShift[]    findAll()
 * @method GuardShift[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GuardShiftRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GuardShift::class);
    }

    public function findByDeleted($startDate, $endDate, $site)
    {
        return $this->createQueryBuilder('c')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('site', $site)
            ->andwhere('c.deletedAt IS NOT NULL')
            ->andwhere('c.deletedAt > :startDate')
            ->andwhere('c.deletedAt < :endDate')
            ->andwhere('c.site = :site')
            ->orderBy('c.deletedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function shiftFinder($site, $user, $admin, $rangeStart, $rangeEnd)
    {
        $allShiftsQuery = $this->createQueryBuilder('g');

        if ($site !== null) {
            $allShiftsQuery->andWhere('g.site = :site');
            $allShiftsQuery->setParameter('site', $site);
        }
        if ($user !== null) {
            $allShiftsQuery->andWhere('g.user = :user');
            $allShiftsQuery->setParameter('user', $user);
        }
        if ($admin !== null) {
            $allShiftsQuery->andWhere('g.admin = :admin');
            $allShiftsQuery->setParameter('admin', $admin);
        }

        $allShiftsQuery->andWhere('g.shift_start > :rangeStart');
        $allShiftsQuery->setParameter('rangeStart', $rangeStart);

        $allShiftsQuery->andWhere('g.shift_end < :rangeEnd');
        $allShiftsQuery->setParameter('rangeEnd', $rangeEnd);

        return $allShiftsQuery->getQuery();
    }
}
