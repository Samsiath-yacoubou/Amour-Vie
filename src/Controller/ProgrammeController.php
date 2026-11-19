<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CentreJeuneRepository;
use App\Repository\EmissionRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\PublicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Section « Nos programmes » (§ 2.4).
 */
class ProgrammeController extends AbstractController
{
    #[Route('/programmes', name: 'programmes', methods: ['GET'])]
    public function liste(ProgrammeRepository $programmeRepository): Response
    {
        return $this->render('programme/liste.html.twig', [
            'programmes' => $programmeRepository->trouverActifs(),
        ]);
    }

    #[Route('/programmes/{slug}', name: 'programme_detail', methods: ['GET'])]
    public function detail(
        string $slug,
        ProgrammeRepository $programmeRepository,
        PublicationRepository $publicationRepository,
        ArticleRepository $articleRepository,
        CentreJeuneRepository $centreJeuneRepository,
        EmissionRepository $emissionRepository,
    ): Response {
        $programme = $programmeRepository->trouverActifParSlug($slug);

        if (null === $programme) {
            throw $this->createNotFoundException('Ce programme n\'existe pas.');
        }

        // Articles et documents sont requêtés séparément : la relation de
        // l'entité contient aussi les brouillons, qui ne doivent pas fuiter.
        $publications = $publicationRepository->rechercherPubliees(
            ['programme' => $programme->getSlug()],
            1,
            6,
        );

        $articles = $articleRepository->rechercherPublies(
            ['programme' => $programme->getSlug()],
            1,
            3,
        );

        return $this->render('programme/detail.html.twig', [
            'programme' => $programme,
            'publications' => $publications,
            'articles' => $articles,
            // Vides pour les piliers qui n'en ont pas (Magazine, Campagnes…) :
            // le template affiche chaque section seulement si elle n'est pas vide.
            'centresJeunes' => $centreJeuneRepository->trouverActifsParProgramme($programme),
            'emissions' => $emissionRepository->trouverPubliesParProgramme($programme),
            'autresProgrammes' => array_filter(
                $programmeRepository->trouverActifs(),
                static fn ($autre) => $autre->getId() !== $programme->getId(),
            ),
        ]);
    }
}
