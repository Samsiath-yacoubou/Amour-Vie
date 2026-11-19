<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evenement>
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    /**
     * @return Evenement[]
     */
    public function trouverAVenir(int $limite = 5): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.publie = true')
            ->andWhere('e.dateDebut >= :aujourdhui')
            ->setParameter('aujourdhui', new \DateTimeImmutable('today'))
            ->orderBy('e.dateDebut', 'ASC')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Evenement[]
     */
    public function trouverPasses(int $limite = 10): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.publie = true')
            ->andWhere('e.dateDebut < :aujourdhui')
            ->setParameter('aujourdhui', new \DateTimeImmutable('today'))
            ->orderBy('e.dateDebut', 'DESC')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
    }

    public function compterAVenir(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.publie = true')
            ->andWhere('e.dateDebut >= :aujourdhui')
            ->setParameter('aujourdhui', new \DateTimeImmutable('today'))
            ->getQuery()
            ->getSingleScalarResult();
    }
}
