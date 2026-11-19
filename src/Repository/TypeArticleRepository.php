<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TypeArticle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeArticle>
 */
class TypeArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeArticle::class);
    }

    /**
     * @return TypeArticle[]
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
