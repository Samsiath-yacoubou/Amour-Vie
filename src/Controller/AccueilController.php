<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\SectionPage;
use App\Enum\TypeEmission;
use App\Repository\AlerteRepository;
use App\Repository\ArticleRepository;
use App\Repository\CentreJeuneRepository;
use App\Repository\ChiffreCleRepository;
use App\Repository\EmissionRepository;
use App\Repository\EvenementRepository;
use App\Repository\PartenaireRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\PublicationRepository;
use App\Repository\SectionPageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Page d'accueil (§ 2.2).
 */
class AccueilController extends AbstractController
{
    /** Sigle du programme « Magazine Amour&Vie », pour retrouver son dernier numéro. */
    private const SIGLE_PROGRAMME_MAGAZINE = 'MAG';

    #[Route('/', name: 'accueil', methods: ['GET'])]
    public function index(
        ChiffreCleRepository $chiffreCleRepository,
        ProgrammeRepository $programmeRepository,
        ArticleRepository $articleRepository,
        PartenaireRepository $partenaireRepository,
        EvenementRepository $evenementRepository,
        SectionPageRepository $sectionPageRepository,
        CentreJeuneRepository $centreJeuneRepository,
        EmissionRepository $emissionRepository,
        PublicationRepository $publicationRepository,
    ): Response {
        // Les articles épinglés priment ; on complète avec les plus récents.
        // Cinq articles : un article « à la une » suivi de quatre en liste.
        $aLaUne = $articleRepository->trouverALaUne(5);
        if (\count($aLaUne) < 5) {
            $aLaUne = $articleRepository->trouverRecents(5);
        }

        $programmeMagazine = $programmeRepository->trouverActifParSigle(self::SIGLE_PROGRAMME_MAGAZINE);
        $dernierMagazine = $programmeMagazine
            ? $publicationRepository->trouverDerniereParProgramme($programmeMagazine)
            : null;

        return $this->render('accueil/index.html.twig', [
            'chiffres' => $chiffreCleRepository->trouverActifs(),
            'programmes' => $programmeRepository->trouverActifs(),
            'articles' => $aLaUne,
            'partenaires' => $partenaireRepository->trouverActifs(),
            'evenements' => $evenementRepository->trouverAVenir(3),
            'sections' => $sectionPageRepository->trouverParPage(SectionPage::PAGE_ACCUEIL),
            'centresJeunes' => $centreJeuneRepository->trouverActifs(),
            'derniereEmissionTv' => $emissionRepository->trouverDerniereParType(TypeEmission::TV),
            'dernierMagazine' => $dernierMagazine,
        ]);
    }
}
