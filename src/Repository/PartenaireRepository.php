<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Partenaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Partenaire>
 */
class PartenaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Partenaire::class);
    }

    /**
     * @return Partenaire[]
     */
    public function trouverActifs(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.actif = true')
            ->orderBy('p.ordre', 'ASC')
            ->addOrderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function compterActifs(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.actif = true')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
