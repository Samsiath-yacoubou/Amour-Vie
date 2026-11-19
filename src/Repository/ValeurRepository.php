<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Valeur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Valeur>
 */
class ValeurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Valeur::class);
    }

    /**
     * @return Valeur[]
     */
    public function trouverActives(): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.actif = true')
            ->orderBy('v.ordre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
