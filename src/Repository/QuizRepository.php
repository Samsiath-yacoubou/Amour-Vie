<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Quiz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quiz>
 */
class QuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quiz::class);
    }

    /**
     * @return Quiz[]
     */
    public function trouverActifs(): array
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.actif = true')
            ->orderBy('q.ordre', 'ASC')
            ->addOrderBy('q.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function trouverActifParSlug(string $slug): ?Quiz
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.actif = true')
            ->andWhere('q.slug = :slug')->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
