<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Programme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Programme>
 */
class ProgrammeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Programme::class);
    }

    /**
     * Programmes actifs, dans l'ordre défini par le back-office.
     *
     * @return Programme[]
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

    public function trouverActifParSlug(string $slug): ?Programme
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.actif = true')
            ->andWhere('p.slug = :slug')->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function trouverActifParSigle(string $sigle): ?Programme
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.actif = true')
            ->andWhere('p.sigle = :sigle')->setParameter('sigle', $sigle)
            ->getQuery()
            ->getOneOrNullResult();
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
