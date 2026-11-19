<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Apprenant;
use App\Entity\Certificat;
use App\Entity\Cours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Certificat>
 */
class CertificatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Certificat::class);
    }

    public function trouverParNumero(string $numero): ?Certificat
    {
        return $this->findOneBy(['numero' => $numero]);
    }

    public function trouverPour(Apprenant $apprenant, Cours $cours): ?Certificat
    {
        return $this->findOneBy(['apprenant' => $apprenant, 'cours' => $cours]);
    }

    /**
     * @return Certificat[]
     */
    public function trouverPourApprenant(Apprenant $apprenant): array
    {
        return $this->findBy(['apprenant' => $apprenant], ['delivreLe' => 'DESC']);
    }

    public function compterValides(?Cours $cours = null): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.revoqueLe IS NULL');

        if (null !== $cours) {
            $qb->andWhere('c.cours = :cours')->setParameter('cours', $cours);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
