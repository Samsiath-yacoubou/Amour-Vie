<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TypePublication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypePublication>
 */
class TypePublicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypePublication::class);
    }

    /**
     * @return TypePublication[]
     */
    public function trouverOrdonnes(): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.ordre', 'ASC')
            ->addOrderBy('t.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
