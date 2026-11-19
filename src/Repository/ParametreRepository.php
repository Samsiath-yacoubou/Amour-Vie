<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Parametre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Parametre>
 */
class ParametreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parametre::class);
    }

    /**
     * Récupère l'unique enregistrement de paramètres, en le créant au besoin.
     * Le site reste ainsi affichable même sur une base fraîchement installée.
     */
    public function obtenir(): Parametre
    {
        $parametre = $this->createQueryBuilder('p')
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $parametre) {
            $parametre = new Parametre();
            $this->getEntityManager()->persist($parametre);
            $this->getEntityManager()->flush();
        }

        return $parametre;
    }
}
