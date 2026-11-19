<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Article;
use App\Enum\StatutArticle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Base commune à toutes les requêtes publiques : un article est visible
     * s'il est publié, ou programmé avec une date déjà atteinte (§ 3.2).
     */
    public function queryBuilderPublies(string $alias = 'a'): QueryBuilder
    {
        return $this->createQueryBuilder($alias)
            ->andWhere(sprintf('%1$s.statut = :publie OR (%1$s.statut = :planifie AND %1$s.datePublication <= :maintenant)', $alias))
            ->setParameter('publie', StatutArticle::PUBLIE)
            ->setParameter('planifie', StatutArticle::PLANIFIE)
            ->setParameter('maintenant', new \DateTimeImmutable())
            ->orderBy($alias.'.datePublication', 'DESC');
    }

    /**
     * Liste paginée et filtrée des actualités (§ 2.5).
     *
     * @param array{programme?: ?string, thematique?: ?string, type?: ?string, annee?: ?int, recherche?: ?string} $filtres
     *
     * @return Paginator<Article>
     */
    public function rechercherPublies(array $filtres = [], int $page = 1, int $parPage = 9): Paginator
    {
        $qb = $this->queryBuilderPublies()
            ->leftJoin('a.programme', 'p')->addSelect('p')
            ->leftJoin('a.type', 't')->addSelect('t')
            ->leftJoin('a.thematiques', 'th')->addSelect('th');

        if (!empty($filtres['programme'])) {
            $qb->andWhere('p.slug = :programme')->setParameter('programme', $filtres['programme']);
        }

        if (!empty($filtres['type'])) {
            $qb->andWhere('t.slug = :type')->setParameter('type', $filtres['type']);
        }

        if (!empty($filtres['thematique'])) {
            // Sous-requête : sans elle, le filtre masquerait les autres thématiques de l'article.
            $qb->andWhere(
                $qb->expr()->exists(
                    'SELECT 1 FROM App\Entity\Article a2 JOIN a2.thematiques th2 WHERE a2 = a AND th2.slug = :thematique'
                )
            )->setParameter('thematique', $filtres['thematique']);
        }

        if (!empty($filtres['annee'])) {
            $qb->andWhere('a.datePublication BETWEEN :debutAnnee AND :finAnnee')
                ->setParameter('debutAnnee', new \DateTimeImmutable(sprintf('%d-01-01 00:00:00', $filtres['annee'])))
                ->setParameter('finAnnee', new \DateTimeImmutable(sprintf('%d-12-31 23:59:59', $filtres['annee'])));
        }

        if (!empty($filtres['recherche'])) {
            $qb->andWhere('a.titre LIKE :recherche OR a.resume LIKE :recherche OR a.contenu LIKE :recherche')
                ->setParameter('recherche', '%'.$filtres['recherche'].'%');
        }

        $qb->setFirstResult(($page - 1) * $parPage)->setMaxResults($parPage);

        return new Paginator($qb->getQuery(), true);
    }

    /**
     * Les N dernières actualités pour la page d'accueil (§ 2.2.D).
     *
     * @return Article[]
     */
    public function trouverRecents(int $limite = 3): array
    {
        return $this->queryBuilderPublies()
            ->leftJoin('a.programme', 'p')->addSelect('p')
            ->leftJoin('a.type', 't')->addSelect('t')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
    }

    /**
     * Articles épinglés « à la une » (§ 3.10).
     *
     * @return Article[]
     */
    public function trouverALaUne(int $limite = 3): array
    {
        return $this->queryBuilderPublies()
            ->andWhere('a.aLaUne = true')
            ->leftJoin('a.programme', 'p')->addSelect('p')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
    }

    public function trouverPublieParSlug(string $slug): ?Article
    {
        return $this->queryBuilderPublies()
            ->andWhere('a.slug = :slug')->setParameter('slug', $slug)
            ->leftJoin('a.programme', 'p')->addSelect('p')
            ->leftJoin('a.type', 't')->addSelect('t')
            ->leftJoin('a.auteur', 'au')->addSelect('au')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Articles proches d'un article donné, pour le bloc « À lire aussi ».
     *
     * @return Article[]
     */
    public function trouverSimilaires(Article $article, int $limite = 3): array
    {
        $qb = $this->queryBuilderPublies()
            ->andWhere('a.id != :id')->setParameter('id', $article->getId())
            ->setMaxResults($limite);

        if (null !== $article->getProgramme()) {
            $qb->andWhere('a.programme = :programme')->setParameter('programme', $article->getProgramme());
        }

        $similaires = $qb->getQuery()->getResult();

        // Complète avec les plus récents si le programme ne fournit pas assez d'articles.
        if (\count($similaires) < $limite) {
            $exclus = array_map(static fn (Article $a) => $a->getId(), $similaires);
            $exclus[] = $article->getId();

            $complement = $this->queryBuilderPublies()
                ->andWhere('a.id NOT IN (:exclus)')->setParameter('exclus', $exclus)
                ->setMaxResults($limite - \count($similaires))
                ->getQuery()
                ->getResult();

            $similaires = array_merge($similaires, $complement);
        }

        return $similaires;
    }

    /**
     * Années disponibles, pour alimenter le filtre par date.
     *
     * @return int[]
     */
    public function trouverAnneesDisponibles(): array
    {
        $resultats = $this->queryBuilderPublies()
            ->select('DISTINCT SUBSTRING(a.datePublication, 1, 4) AS annee')
            ->resetDQLPart('orderBy')
            ->orderBy('annee', 'DESC')
            ->getQuery()
            ->getScalarResult();

        return array_map(static fn (array $ligne) => (int) $ligne['annee'], $resultats);
    }

    public function compterParStatut(StatutArticle $statut): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.statut = :statut')->setParameter('statut', $statut)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function incrementerVues(Article $article): void
    {
        // Mise à jour directe : évite de déclencher le Timestampable sur une simple lecture.
        $this->getEntityManager()->createQuery(
            'UPDATE App\Entity\Article a SET a.nombreVues = a.nombreVues + 1 WHERE a.id = :id'
        )->setParameter('id', $article->getId())->execute();
    }
}
