<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CentreJeuneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Annuaire et carte interactive des Centres Jeunes Amour&Vie.
 */
class CentreJeuneController extends AbstractController
{
    #[Route('/centres-jeunes', name: 'centres_jeunes', methods: ['GET'])]
    public function liste(CentreJeuneRepository $centreJeuneRepository): Response
    {
        return $this->render('centre_jeune/liste.html.twig', [
            'centres' => $centreJeuneRepository->trouverActifs(),
            'centresCarte' => $centreJeuneRepository->trouverPourCarte(),
        ]);
    }

    #[Route('/centres-jeunes/{slug}', name: 'centre_jeune_detail', methods: ['GET'])]
    public function detail(string $slug, CentreJeuneRepository $centreJeuneRepository): Response
    {
        $centre = $centreJeuneRepository->trouverActifParSlug($slug);

        if (null === $centre) {
            throw $this->createNotFoundException('Ce centre jeune n\'existe pas.');
        }

        return $this->render('centre_jeune/detail.html.twig', [
            'centre' => $centre,
        ]);
    }
}
