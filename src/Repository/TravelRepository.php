<?php

namespace App\Repository;

use App\Entity\Travel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Travel|null find($id, $lockMode = null, $lockVersion = null)
 * @method Travel|null findOneBy(array $criteria, array $orderBy = null)
 * @method Travel[]    findAll()
 * @method Travel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TravelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Travel::class);
    }

    /**
     * Tous les voyages d'un utilisateur qui sont visibles
     *
     * @param integer $userId
     * @param boolean $visibility
     * @return void
     */
    public function findByUserAndVisibility(int $userId, bool $visibility)
    {
        return $this->createQueryBuilder('travels')
            ->select('travels.id', 'travels.title', 'travels.cover')
            ->andWhere('travels.visibility = :val')
            ->setParameter('val', $visibility)
            ->andWhere('travels.user = :val2')
            ->setParameter('val2', $userId)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Tous les ID des voyages visisibles
     *
     * @return array
     */
    public function findAllTravelsIdByVisibility(): array
    {
        return $this->createQueryBuilder('travels')
            ->select('travels.id', 'travels.visibility')
            ->andWhere('travels.visibility = :val')
            ->setParameter('val', true)
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Travel[] Returns an array of Travel objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Travel
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
