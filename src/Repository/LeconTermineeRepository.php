<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Apprenant;
use App\Entity\Cours;
use App\Entity\LeconTerminee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LeconTerminee>
 */
class LeconTermineeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LeconTerminee::class);
    }

    /**
     * Leçons du cours déjà terminées par l'apprenant.
     *
     * @return array<int, true> indexé par identifiant de leçon
     */
    public function idsTerminees(Apprenant $apprenant, Cours $cours): array
    {
        $ids = $this->createQueryBuilder('lt')
            ->select('IDENTITY(lt.lecon) AS id')
            ->join('lt.lecon', 'l')
            ->join('l.module', 'm')
            ->andWhere('lt.apprenant = :apprenant')->setParameter('apprenant', $apprenant)
            ->andWhere('m.cours = :cours')->setParameter('cours', $cours)
            ->getQuery()
            ->getSingleColumnResult();

        return array_fill_keys(array_map('intval', $ids), true);
    }
}
