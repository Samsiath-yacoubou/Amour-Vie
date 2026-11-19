<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\SectionPage;
use App\Entity\Temoignage;
use App\Form\TemoignageType;
use App\Repository\SectionPageRepository;
use App\Repository\TemoignageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Section « Témoignages » : lecture publique et soumission anonyme modérée.
 */
class TemoignageController extends AbstractController
{
    /** Nombre maximal de témoignages acceptés par IP sur une heure glissante. */
    private const LIMITE_PAR_HEURE = 5;
    private const PAR_PAGE = 9;

    #[Route('/temoignages', name: 'temoignages', methods: ['GET'])]
    public function liste(Request $request, TemoignageRepository $temoignageRepository): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $paginateur = $temoignageRepository->trouverPublies($page, self::PAR_PAGE);
        $total = \count($paginateur);
        $totalPages = max(1, (int) ceil($total / self::PAR_PAGE));

        if ($page > $totalPages && $total > 0) {
            throw $this->createNotFoundException('Cette page de résultats n\'existe pas.');
        }

        return $this->render('temoignage/liste.html.twig', [
            'temoignages' => $paginateur,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/temoignages/partager', name: 'temoignage_partager', methods: ['GET', 'POST'])]
    public function partager(
        Request $request,
        EntityManagerInterface $entityManager,
        TemoignageRepository $temoignageRepository,
        SectionPageRepository $sectionPageRepository,
    ): Response {
        $temoignage = new Temoignage();
        $formulaire = $this->createForm(TemoignageType::class, $temoignage);
        $formulaire->handleRequest($request);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $ip = $request->getClientIp();

            // Le pot de miel n'est jamais rempli par un visiteur légitime :
            // on répond comme si tout allait bien pour ne pas informer le robot.
            $estSpam = '' !== trim((string) $formulaire->get('siteWeb')->getData());

            $tropDeMessages = null !== $ip && $temoignageRepository->compterDepuisIp(
                $ip,
                new \DateTimeImmutable('-1 hour'),
            ) >= self::LIMITE_PAR_HEURE;

            if ($tropDeMessages) {
                $this->addFlash('warning', 'Merci de patienter avant de partager un nouveau témoignage.');

                return $this->redirectToRoute('temoignage_partager');
            }

            if (!$estSpam) {
                $temoignage->setAdresseIp($ip);
                $entityManager->persist($temoignage);
                $entityManager->flush();
            }

            $this->addFlash('success', 'Merci pour ton témoignage ! Il sera publié après relecture par notre équipe.');

            return $this->redirectToRoute('temoignage_partager');
        }

        return $this->render('temoignage/partager.html.twig', [
            'formulaire' => $formulaire,
            'sections' => $sectionPageRepository->trouverParPage(SectionPage::PAGE_TEMOIGNAGES),
        ]);
    }
}
