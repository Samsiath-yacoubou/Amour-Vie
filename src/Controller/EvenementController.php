<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Evenement;
use App\Enum\TypeMedia;
use App\Repository\EvenementRepository;
use App\Repository\GalerieRepository;
use App\Repository\MediaRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Agenda (§ 3.8) et médiathèque publique (§ 3.11).
 */
class EvenementController extends AbstractController
{
    #[Route('/evenements', name: 'evenements', methods: ['GET'])]
    public function agenda(EvenementRepository $evenementRepository): Response
    {
        return $this->render('evenement/liste.html.twig', [
            'aVenir' => $evenementRepository->trouverAVenir(20),
            'passes' => $evenementRepository->trouverPasses(10),
        ]);
    }

    #[Route('/evenements/{slug}', name: 'evenement_detail', methods: ['GET'])]
    public function detail(
        #[MapEntity(mapping: ['slug' => 'slug'])] Evenement $evenement,
        EvenementRepository $evenementRepository,
    ): Response {
        if (!$evenement->isPublie()) {
            throw $this->createNotFoundException('Cet événement n\'est pas disponible.');
        }

        return $this->render('evenement/detail.html.twig', [
            'evenement' => $evenement,
            'autres' => array_filter(
                $evenementRepository->trouverAVenir(4),
                static fn (Evenement $autre) => $autre->getId() !== $evenement->getId(),
            ),
        ]);
    }

    #[Route('/mediatheque', name: 'mediatheque', methods: ['GET'])]
    public function mediatheque(
        MediaRepository $mediaRepository,
        GalerieRepository $galerieRepository,
    ): Response {
        return $this->render('evenement/mediatheque.html.twig', [
            'images' => $mediaRepository->trouverParType(TypeMedia::IMAGE, 24),
            'videos' => $mediaRepository->trouverParType(TypeMedia::VIDEO, 8),
            'galeries' => $galerieRepository->findBy([], ['createdAt' => 'DESC'], 12),
        ]);
    }
}
