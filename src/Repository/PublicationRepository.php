<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Programme;
use App\Entity\Publication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Publication>
 */
class PublicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Publication::class);
    }

    public function queryBuilderPubliees(string $alias = 'p'): QueryBuilder
    {
        return $this->createQueryBuilder($alias)
            ->andWhere($alias.'.publie = true')
            ->orderBy($alias.'.annee', 'DESC')
            ->addOrderBy($alias.'.createdAt', 'DESC');
    }

    /**
     * Bibliothèque numérique filtrée par année, programme, type et thématique (§ 2.6).
     *
     * @param array{annee?: ?int, programme?: ?string, type?: ?string, thematique?: ?string, recherche?: ?string} $filtres
     *
     * @return Paginator<Publication>
     */
    public function rechercherPubliees(array $filtres = [], int $page = 1, int $parPage = 12): Paginator
    {
        $qb = $this->queryBuilderPubliees()
            ->leftJoin('p.type', 't')->addSelect('t')
            ->leftJoin('p.programme', 'pr')->addSelect('pr');

        if (!empty($filtres['annee'])) {
            $qb->andWhere('p.annee = :annee')->setParameter('annee', $filtres['annee']);
        }

        if (!empty($filtres['programme'])) {
            $qb->andWhere('pr.slug = :programme')->setParameter('programme', $filtres['programme']);
        }

        if (!empty($filtres['type'])) {
            $qb->andWhere('t.slug = :type')->setParameter('type', $filtres['type']);
        }

        if (!empty($filtres['thematique'])) {
            $qb->andWhere(
                $qb->expr()->exists(
                    'SELECT 1 FROM App\Entity\Publication p2 JOIN p2.thematiques th2 WHERE p2 = p AND th2.slug = :thematique'
                )
            )->setParameter('thematique', $filtres['thematique']);
        }

        if (!empty($filtres['recherche'])) {
            $qb->andWhere('p.titre LIKE :recherche OR p.description LIKE :recherche')
                ->setParameter('recherche', '%'.$filtres['recherche'].'%');
        }

        $qb->setFirstResult(($page - 1) * $parPage)->setMaxResults($parPage);

        return new Paginator($qb->getQuery(), true);
    }

    /**
     * @return Publication[]
     */
    public function trouverRecentes(int $limite = 4): array
    {
        return $this->queryBuilderPubliees()
            ->leftJoin('p.type', 't')->addSelect('t')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
    }

    /**
     * Dernier document publié d'un programme donné, pour mise en avant
     * sur la page d'accueil (ex. dernier numéro du magazine).
     */
    public function trouverDerniereParProgramme(Programme $programme): ?Publication
    {
        return $this->queryBuilderPubliees()
            ->andWhere('p.programme = :programme')->setParameter('programme', $programme)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Années présentes en base, pour le filtre correspondant.
     *
     * @return int[]
     */
    public function trouverAnneesDisponibles(): array
    {
        $resultats = $this->createQueryBuilder('p')
            ->select('DISTINCT p.annee')
            ->andWhere('p.publie = true')
            ->orderBy('p.annee', 'DESC')
            ->getQuery()
            ->getScalarResult();

        return array_map(static fn (array $ligne) => (int) $ligne['annee'], $resultats);
    }

    public function incrementerTelechargements(Publication $publication): void
    {
        $this->getEntityManager()->createQuery(
            'UPDATE App\Entity\Publication p SET p.nombreTelechargements = p.nombreTelechargements + 1 WHERE p.id = :id'
        )->setParameter('id', $publication->getId())->execute();
    }
}
