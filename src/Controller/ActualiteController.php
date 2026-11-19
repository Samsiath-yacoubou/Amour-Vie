<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\ThematiqueRepository;
use App\Repository\TypeArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Section « Actualités » : liste filtrable et page de détail (§ 2.5).
 */
class ActualiteController extends AbstractController
{
    private const PAR_PAGE = 9;

    #[Route('/actualites', name: 'actualites', methods: ['GET'])]
    public function liste(
        Request $request,
        ArticleRepository $articleRepository,
        ProgrammeRepository $programmeRepository,
        ThematiqueRepository $thematiqueRepository,
        TypeArticleRepository $typeArticleRepository,
    ): Response {
        $filtres = [
            'programme' => $request->query->get('programme'),
            'thematique' => $request->query->get('thematique'),
            'type' => $request->query->get('type'),
            // filter() plutôt que getInt() : le formulaire envoie « annee= » vide quand
            // aucune année n'est choisie, ce que getInt() rejette par une erreur 400.
            'annee' => $request->query->filter('annee', null, \FILTER_VALIDATE_INT, ['flags' => \FILTER_NULL_ON_FAILURE]) ?: null,
            'recherche' => trim((string) $request->query->get('q')) ?: null,
        ];

        $page = max(1, $request->query->getInt('page', 1));
        $paginateur = $articleRepository->rechercherPublies($filtres, $page, self::PAR_PAGE);
        $total = \count($paginateur);
        $totalPages = max(1, (int) ceil($total / self::PAR_PAGE));

        // Une page hors bornes doit répondre 404 plutôt que d'afficher une liste vide.
        if ($page > $totalPages && $total > 0) {
            throw $this->createNotFoundException('Cette page de résultats n\'existe pas.');
        }

        return $this->render('actualite/liste.html.twig', [
            'articles' => $paginateur,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'filtres' => $filtres,
            'programmes' => $programmeRepository->trouverActifs(),
            'thematiques' => $thematiqueRepository->trouverOrdonnees(),
            'types' => $typeArticleRepository->trouverOrdonnes(),
            'annees' => $articleRepository->trouverAnneesDisponibles(),
        ]);
    }

    #[Route('/actualites/{slug}', name: 'actualite_detail', methods: ['GET'])]
    public function detail(string $slug, Request $request, ArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->trouverPublieParSlug($slug);

        if (null === $article) {
            throw $this->createNotFoundException('Cette actualité n\'existe pas ou n\'est plus disponible.');
        }

        // Un simple préchargement au survol (navigation instantanée) n'est pas
        // une lecture : la vue sera confirmée si le visiteur ouvre la page.
        $prechargement = $request->headers->has('X-Prechargement');
        if (!$prechargement) {
            $articleRepository->incrementerVues($article);
        }

        $reponse = $this->render('actualite/detail.html.twig', [
            'article' => $article,
            'similaires' => $articleRepository->trouverSimilaires($article, 3),
        ]);

        if ($prechargement) {
            $reponse->headers->set('X-Visite-A-Confirmer', '1');
        }

        return $reponse;
    }
}
