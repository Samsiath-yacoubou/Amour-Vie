<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Thematique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Thematique>
 */
class ThematiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Thematique::class);
    }

    /**
     * @return Thematique[]
     */
    public function trouverOrdonnees(): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.ordre', 'ASC')
            ->addOrderBy('t.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
