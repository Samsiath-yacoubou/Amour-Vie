<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Temoignage;
use App\Enum\StatutModeration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Temoignage>
 */
class TemoignageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Temoignage::class);
    }

    /**
     * @return Paginator<Temoignage>
     */
    public function trouverPublies(int $page = 1, int $parPage = 9): Paginator
    {
        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.statut = :statut')->setParameter('statut', StatutModeration::PUBLIE)
            ->orderBy('t.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $parPage)
            ->setMaxResults($parPage);

        return new Paginator($qb->getQuery(), false);
    }

    public function compterEnAttente(): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.statut = :statut')->setParameter('statut', StatutModeration::EN_ATTENTE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Garde-fou anti-spam : nombre de témoignages envoyés depuis une IP
     * sur une fenêtre glissante.
     */
    public function compterDepuisIp(string $ip, \DateTimeImmutable $depuis): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.adresseIp = :ip')->setParameter('ip', $ip)
            ->andWhere('t.createdAt >= :depuis')->setParameter('depuis', $depuis)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
