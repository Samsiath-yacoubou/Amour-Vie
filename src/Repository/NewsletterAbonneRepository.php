<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\NewsletterAbonne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NewsletterAbonne>
 */
class NewsletterAbonneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsletterAbonne::class);
    }

    public function estDejaAbonne(string $email): bool
    {
        return null !== $this->createQueryBuilder('n')
            ->andWhere('n.email = :email')->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function compterActifs(): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('n.actif = true')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return NewsletterAbonne[]
     */
    public function trouverTousActifs(): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.actif = true')
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
