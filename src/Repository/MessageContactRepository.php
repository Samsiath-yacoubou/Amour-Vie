<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MessageContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MessageContact>
 */
class MessageContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageContact::class);
    }

    public function compterNonLus(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.lu = false')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Garde-fou anti-spam : nombre de messages envoyés depuis une IP
     * sur une fenêtre glissante.
     */
    public function compterDepuisIp(string $ip, \DateTimeImmutable $depuis): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.adresseIp = :ip')->setParameter('ip', $ip)
            ->andWhere('m.createdAt >= :depuis')->setParameter('depuis', $depuis)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
