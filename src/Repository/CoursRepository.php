<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Cours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cours>
 */
class CoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cours::class);
    }

    /**
     * @return Cours[]
     */
    public function trouverPublies(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.publie = true')
            ->orderBy('c.ordre', 'ASC')
            ->addOrderBy('c.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** Charge le cours avec ses modules et leçons en une requête. */
    public function trouverPublieParSlug(string $slug): ?Cours
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.modules', 'm')->addSelect('m')
            ->leftJoin('m.lecons', 'l')->addSelect('l')
            ->andWhere('c.publie = true')
            ->andWhere('c.slug = :slug')->setParameter('slug', $slug)
            ->orderBy('m.ordre', 'ASC')
            ->addOrderBy('m.id', 'ASC')
            ->addOrderBy('l.ordre', 'ASC')
            ->addOrderBy('l.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
