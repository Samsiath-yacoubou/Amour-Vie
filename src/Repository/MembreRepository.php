<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Membre;
use App\Enum\CategorieMembre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Membre>
 */
class MembreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Membre::class);
    }

    /**
     * @return Membre[]
     */
    public function trouverParCategorie(CategorieMembre $categorie): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.actif = true')
            ->andWhere('m.categorie = :categorie')->setParameter('categorie', $categorie)
            ->orderBy('m.ordre', 'ASC')
            ->addOrderBy('m.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Tous les membres actifs, groupés par catégorie pour la page « Qui sommes-nous ? ».
     *
     * @return array<string, Membre[]>
     */
    public function trouverGroupesParCategorie(): array
    {
        $groupes = [];
        foreach (CategorieMembre::cases() as $categorie) {
            $membres = $this->trouverParCategorie($categorie);
            if ([] !== $membres) {
                $groupes[$categorie->value] = $membres;
            }
        }

        return $groupes;
    }

    public function compterActifs(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.actif = true')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
