<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Apprenant;
use App\Entity\Cours;
use App\Entity\TentativeExamen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TentativeExamen>
 */
class TentativeExamenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TentativeExamen::class);
    }

    /**
     * Bilan de l'apprenant à l'examen final du cours.
     *
     * @return array{tentatives: int, meilleur: ?int, reussi: bool}
     */
    public function bilan(Apprenant $apprenant, Cours $cours): array
    {
        $tentatives = $this->findBy(['apprenant' => $apprenant, 'cours' => $cours]);

        $meilleur = null;
        $reussi = false;
        foreach ($tentatives as $tentative) {
            $meilleur = max($meilleur ?? 0, $tentative->getPourcentage());
            $reussi = $reussi || $tentative->isReussie();
        }

        return ['tentatives' => \count($tentatives), 'meilleur' => $meilleur, 'reussi' => $reussi];
    }

    /** Meilleur pourcentage obtenu (pour l'inscrire sur le certificat). */
    public function meilleurPourcentage(Apprenant $apprenant, Cours $cours): ?int
    {
        return $this->bilan($apprenant, $cours)['meilleur'];
    }

    /** Remet à zéro les tentatives (décision de l'équipe, ex. après un problème technique). */
    public function supprimerPour(Apprenant $apprenant, Cours $cours): int
    {
        return $this->createQueryBuilder('t')
            ->delete()
            ->andWhere('t.apprenant = :apprenant')->setParameter('apprenant', $apprenant)
            ->andWhere('t.cours = :cours')->setParameter('cours', $cours)
            ->getQuery()
            ->execute();
    }
}
