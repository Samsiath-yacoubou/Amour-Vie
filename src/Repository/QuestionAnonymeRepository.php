<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\QuestionAnonyme;
use App\Enum\StatutQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuestionAnonyme>
 */
class QuestionAnonymeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuestionAnonyme::class);
    }

    /**
     * FAQ publique : questions répondues et explicitement publiées.
     *
     * @return QuestionAnonyme[]
     */
    public function trouverFaqPublique(?string $thematiqueSlug = null): array
    {
        $qb = $this->createQueryBuilder('q')
            ->andWhere('q.statut = :statut')->setParameter('statut', StatutQuestion::PUBLIEE_FAQ)
            ->orderBy('q.ordre', 'ASC')
            ->addOrderBy('q.createdAt', 'DESC');

        if (null !== $thematiqueSlug) {
            $qb->join('q.thematiques', 'th')
                ->andWhere('th.slug = :thematique')->setParameter('thematique', $thematiqueSlug);
        }

        return $qb->getQuery()->getResult();
    }

    public function compterEnAttente(): int
    {
        return (int) $this->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->andWhere('q.statut = :statut')->setParameter('statut', StatutQuestion::EN_ATTENTE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Garde-fou anti-spam : nombre de questions envoyées depuis une IP
     * sur une fenêtre glissante.
     */
    public function compterDepuisIp(string $ip, \DateTimeImmutable $depuis): int
    {
        return (int) $this->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->andWhere('q.adresseIp = :ip')->setParameter('ip', $ip)
            ->andWhere('q.createdAt >= :depuis')->setParameter('depuis', $depuis)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
