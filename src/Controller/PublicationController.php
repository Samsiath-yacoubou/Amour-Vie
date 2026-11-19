<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Publication;
use App\Repository\ProgrammeRepository;
use App\Repository\PublicationRepository;
use App\Repository\ThematiqueRepository;
use App\Repository\TypePublicationRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * Bibliothèque numérique (§ 2.6).
 */
class PublicationController extends AbstractController
{
    private const PAR_PAGE = 12;

    #[Route('/publications', name: 'publications', methods: ['GET'])]
    public function liste(
        Request $request,
        PublicationRepository $publicationRepository,
        ProgrammeRepository $programmeRepository,
        ThematiqueRepository $thematiqueRepository,
        TypePublicationRepository $typePublicationRepository,
    ): Response {
        $filtres = [
            // filter() plutôt que getInt() : le formulaire envoie « annee= » vide quand
            // aucune année n'est choisie, ce que getInt() rejette par une erreur 400.
            'annee' => $request->query->filter('annee', null, \FILTER_VALIDATE_INT, ['flags' => \FILTER_NULL_ON_FAILURE]) ?: null,
            'programme' => $request->query->get('programme'),
            'type' => $request->query->get('type'),
            'thematique' => $request->query->get('thematique'),
            'recherche' => trim((string) $request->query->get('q')) ?: null,
        ];

        $page = max(1, $request->query->getInt('page', 1));
        $paginateur = $publicationRepository->rechercherPubliees($filtres, $page, self::PAR_PAGE);
        $total = \count($paginateur);

        return $this->render('publication/liste.html.twig', [
            'publications' => $paginateur,
            'total' => $total,
            'page' => $page,
            'totalPages' => max(1, (int) ceil($total / self::PAR_PAGE)),
            'filtres' => $filtres,
            'programmes' => $programmeRepository->trouverActifs(),
            'thematiques' => $thematiqueRepository->trouverOrdonnees(),
            'types' => $typePublicationRepository->trouverOrdonnes(),
            'annees' => $publicationRepository->trouverAnneesDisponibles(),
        ]);
    }

    #[Route('/publications/{slug}', name: 'publication_detail', methods: ['GET'])]
    public function detail(
        #[MapEntity(mapping: ['slug' => 'slug'])] Publication $publication,
        PublicationRepository $publicationRepository,
    ): Response
    {
        if (!$publication->isPublie()) {
            throw $this->createNotFoundException('Cette publication n\'est pas disponible.');
        }

        return $this->render('publication/detail.html.twig', [
            'publication' => $publication,
            'similaires' => $publicationRepository->rechercherPubliees(
                ['type' => $publication->getType()?->getSlug()],
                1,
                4,
            ),
        ]);
    }

    /**
     * Sert le fichier et comptabilise le téléchargement (§ 3.3).
     * Le fichier transite par le contrôleur pour que le compteur soit fiable.
     */
    #[Route('/publications/{slug}/telecharger', name: 'publication_telecharger', methods: ['GET'])]
    public function telecharger(
        #[MapEntity(mapping: ['slug' => 'slug'])] Publication $publication,
        PublicationRepository $publicationRepository,
        StorageInterface $storage,
    ): Response {
        if (!$publication->isPublie() || null === $publication->getFichierName()) {
            throw $this->createNotFoundException('Aucun fichier n\'est associé à cette publication.');
        }

        $chemin = $storage->resolvePath($publication, 'fichierFile');

        if (null === $chemin || !is_file($chemin)) {
            throw $this->createNotFoundException('Le fichier est introuvable sur le serveur.');
        }

        $publicationRepository->incrementerTelechargements($publication);

        $reponse = new BinaryFileResponse($chemin);
        $reponse->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $publication->getFichierOriginal() ?? $publication->getFichierName(),
        );

        return $reponse;
    }
}
