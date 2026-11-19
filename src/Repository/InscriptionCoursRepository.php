<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Apprenant;
use App\Entity\Cours;
use App\Entity\InscriptionCours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InscriptionCours>
 */
class InscriptionCoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InscriptionCours::class);
    }

    public function trouver(Apprenant $apprenant, Cours $cours): ?InscriptionCours
    {
        return $this->findOneBy(['apprenant' => $apprenant, 'cours' => $cours]);
    }

    /**
     * Cours suivis par l'apprenant, les plus récemment actifs en premier.
     *
     * @return InscriptionCours[]
     */
    public function trouverPourApprenant(Apprenant $apprenant): array
    {
        return $this->createQueryBuilder('i')
            ->join('i.cours', 'c')->addSelect('c')
            ->andWhere('i.apprenant = :apprenant')->setParameter('apprenant', $apprenant)
            ->orderBy('i.updatedAt', 'DESC')
            ->addOrderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
