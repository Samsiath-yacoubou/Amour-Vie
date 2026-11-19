<?php

declare(strict_types=1);

namespace App\Elearning;

use App\Entity\Apprenant;
use App\Entity\Certificat;
use App\Entity\Cours;
use App\Entity\InscriptionCours;
use App\Entity\TentativeExamen;
use App\Entity\TentativeQuiz;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Chiffres d'usage de l'e-learning pour les rapports de l'ABMS : inscrits,
 * achèvement, scores et certificats, globalement et par cours.
 *
 * Toutes les valeurs sont agrégées : aucun résultat individuel n'en ressort.
 */
final class StatistiquesElearning
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return array{apprenants: int, actifs30j: int, nouveaux30j: int, inscriptions: int, terminees: int, tauxAchevement: ?int, scoreMoyenQuiz: ?int, scoreMoyenExamen: ?int, certificats: int}
     */
    public function globales(): array
    {
        $depuis30j = new \DateTimeImmutable('-30 days');

        $inscriptions = $this->compter(InscriptionCours::class);
        $terminees = $this->compter(InscriptionCours::class, 'e.termineeAt IS NOT NULL');

        return [
            'apprenants' => $this->compter(Apprenant::class),
            'actifs30j' => $this->compter(Apprenant::class, 'e.derniereConnexion >= :depuis', ['depuis' => $depuis30j]),
            'nouveaux30j' => $this->compter(Apprenant::class, 'e.createdAt >= :depuis', ['depuis' => $depuis30j]),
            'inscriptions' => $inscriptions,
            'terminees' => $terminees,
            'tauxAchevement' => $inscriptions > 0 ? (int) round($terminees * 100 / $inscriptions) : null,
            'scoreMoyenQuiz' => $this->scoreMoyen(TentativeQuiz::class),
            'scoreMoyenExamen' => $this->scoreMoyen(TentativeExamen::class),
            'certificats' => $this->compter(Certificat::class, 'e.revoqueLe IS NULL'),
        ];
    }

    /**
     * @return list<array{cours: Cours, inscrits: int, terminees: int, tauxAchevement: ?int, scoreMoyenExamen: ?int, certificats: int}>
     */
    public function parCours(): array
    {
        $cours = $this->entityManager->getRepository(Cours::class)->findBy([], ['ordre' => 'ASC', 'titre' => 'ASC']);

        $lignes = [];
        foreach ($cours as $unCours) {
            $parametres = ['cours' => $unCours];
            $inscrits = $this->compter(InscriptionCours::class, 'e.cours = :cours', $parametres);
            $terminees = $this->compter(InscriptionCours::class, 'e.cours = :cours AND e.termineeAt IS NOT NULL', $parametres);

            $lignes[] = [
                'cours' => $unCours,
                'inscrits' => $inscrits,
                'terminees' => $terminees,
                'tauxAchevement' => $inscrits > 0 ? (int) round($terminees * 100 / $inscrits) : null,
                'scoreMoyenExamen' => $this->scoreMoyen(TentativeExamen::class, 'e.cours = :cours', $parametres),
                'certificats' => $this->compter(Certificat::class, 'e.cours = :cours AND e.revoqueLe IS NULL', $parametres),
            ];
        }

        return $lignes;
    }

    /**
     * Inscriptions des 6 derniers mois, mois par mois (du plus ancien au plus récent).
     *
     * @return list<array{mois: \DateTimeImmutable, inscrits: int}>
     */
    public function inscriptionsParMois(int $nombreMois = 6): array
    {
        $mois = [];
        $debut = new \DateTimeImmutable('first day of this month 00:00');
        for ($i = $nombreMois - 1; $i >= 0; --$i) {
            $de = $debut->modify(sprintf('-%d months', $i));
            $mois[] = [
                'mois' => $de,
                'inscrits' => $this->compter(Apprenant::class, 'e.createdAt >= :de AND e.createdAt < :a', [
                    'de' => $de,
                    'a' => $de->modify('+1 month'),
                ]),
            ];
        }

        return $mois;
    }

    /** @param array<string, mixed> $parametres */
    private function compter(string $entite, ?string $condition = null, array $parametres = []): int
    {
        $qb = $this->entityManager->createQueryBuilder()->select('COUNT(e.id)')->from($entite, 'e');
        if (null !== $condition) {
            $qb->andWhere($condition);
        }
        foreach ($parametres as $nom => $valeur) {
            $qb->setParameter($nom, $valeur);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Score moyen en pourcentage sur toutes les tentatives.
     *
     * @param array<string, mixed> $parametres
     */
    private function scoreMoyen(string $entite, ?string $condition = null, array $parametres = []): ?int
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('SUM(e.score) AS score', 'SUM(e.total) AS total')
            ->from($entite, 'e');
        if (null !== $condition) {
            $qb->andWhere($condition);
        }
        foreach ($parametres as $nom => $valeur) {
            $qb->setParameter($nom, $valeur);
        }

        $resultat = $qb->getQuery()->getSingleResult();
        $total = (int) $resultat['total'];

        return $total > 0 ? (int) round((int) $resultat['score'] * 100 / $total) : null;
    }
}
