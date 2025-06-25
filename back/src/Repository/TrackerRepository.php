<?php

namespace App\Repository;

use App\Entity\Tracker;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tracker>
 */
class TrackerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tracker::class);
    }

    public function findByUserAndDate(User $user, \DateTime $date): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :userId')
            ->andWhere('t.datetime >= :startOfDay')
            ->andWhere('t.datetime <= :endOfDay')
            ->setParameter('userId', $user->getId())
            ->setParameter('startOfDay', $date->setTime(0, 0, 0))
            ->setParameter('endOfDay', $date->setTime(23, 59, 59))
            ->orderBy('t.datetime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Tracker[] Returns an array of Tracker objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Tracker
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
