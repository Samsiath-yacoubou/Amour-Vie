<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Apprenant;
use App\Entity\Cours;
use App\Entity\TentativeQuiz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TentativeQuiz>
 */
class TentativeQuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TentativeQuiz::class);
    }

    /**
     * Meilleur pourcentage obtenu à chaque module du cours, et réussite éventuelle.
     *
     * @return array<int, array{meilleur: int, reussi: bool}> indexé par identifiant de module
     */
    public function bilanParModule(Apprenant $apprenant, Cours $cours): array
    {
        $tentatives = $this->createQueryBuilder('t')
            ->join('t.module', 'm')
            ->andWhere('t.apprenant = :apprenant')->setParameter('apprenant', $apprenant)
            ->andWhere('m.cours = :cours')->setParameter('cours', $cours)
            ->getQuery()
            ->getResult();

        $bilan = [];
        foreach ($tentatives as $tentative) {
            $id = $tentative->getModule()->getId();
            $bilan[$id] ??= ['meilleur' => 0, 'reussi' => false];
            $bilan[$id]['meilleur'] = max($bilan[$id]['meilleur'], $tentative->getPourcentage());
            $bilan[$id]['reussi'] = $bilan[$id]['reussi'] || $tentative->isReussie();
        }

        return $bilan;
    }
}
