<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\EmissionRepository;
use App\Repository\ThematiqueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Section « Émissions » : radio et TV Amour&Vie.
 */
class EmissionController extends AbstractController
{
    private const PAR_PAGE = 9;

    #[Route('/emissions', name: 'emissions', methods: ['GET'])]
    public function liste(
        Request $request,
        EmissionRepository $emissionRepository,
        ThematiqueRepository $thematiqueRepository,
    ): Response {
        $filtres = [
            'type' => $request->query->get('type'),
            'thematique' => $request->query->get('thematique'),
        ];

        $page = max(1, $request->query->getInt('page', 1));
        $paginateur = $emissionRepository->rechercherPubliees($filtres, $page, self::PAR_PAGE);
        $total = \count($paginateur);
        $totalPages = max(1, (int) ceil($total / self::PAR_PAGE));

        if ($page > $totalPages && $total > 0) {
            throw $this->createNotFoundException('Cette page de résultats n\'existe pas.');
        }

        return $this->render('emission/liste.html.twig', [
            'emissions' => $paginateur,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'filtres' => $filtres,
            'thematiques' => $thematiqueRepository->trouverOrdonnees(),
        ]);
    }

    #[Route('/emissions/{slug}', name: 'emission_detail', methods: ['GET'])]
    public function detail(string $slug, EmissionRepository $emissionRepository): Response
    {
        $emission = $emissionRepository->findOneBy(['slug' => $slug, 'publie' => true]);

        if (null === $emission) {
            throw $this->createNotFoundException('Cette émission n\'existe pas ou n\'est plus disponible.');
        }

        return $this->render('emission/detail.html.twig', [
            'emission' => $emission,
        ]);
    }
}
