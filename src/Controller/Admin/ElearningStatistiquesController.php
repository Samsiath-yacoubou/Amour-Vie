<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Elearning\ProgressionCours;
use App\Elearning\StatistiquesElearning;
use App\Repository\CertificatRepository;
use App\Repository\InscriptionCoursRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Tableau de bord et export de l'e-learning, pour les rapports de l'ABMS.
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
#[AdminRoute('/elearning', name: 'elearning')]
class ElearningStatistiquesController extends AbstractController
{
    #[AdminRoute('/statistiques', name: 'statistiques')]
    public function statistiques(StatistiquesElearning $statistiques): Response
    {
        return $this->render('admin/elearning_statistiques.html.twig', [
            'globales' => $statistiques->globales(),
            'parCours' => $statistiques->parCours(),
            'parMois' => $statistiques->inscriptionsParMois(),
        ]);
    }

    /**
     * Export CSV (Excel) du suivi : une ligne par inscription. Données
     * personnelles : réservé aux éditeurs.
     */
    #[IsGranted('ROLE_EDITEUR')]
    #[AdminRoute('/export', name: 'export')]
    public function export(
        InscriptionCoursRepository $inscriptionRepository,
        CertificatRepository $certificatRepository,
        ProgressionCours $progression,
    ): StreamedResponse {
        $reponse = new StreamedResponse(function () use ($inscriptionRepository, $certificatRepository, $progression): void {
            $sortie = fopen('php://output', 'w');
            // BOM UTF-8 : Excel affiche correctement les accents.
            fwrite($sortie, "\xEF\xBB\xBF");

            fputcsv($sortie, [
                'Prénom', 'Nom', 'E-mail', 'Cours', 'Inscrit le', 'Progression (%)',
                'Terminé le', 'Meilleur score examen (%)', 'Certificat', 'Statut du certificat',
            ], ';', '"', '');

            $inscriptions = $inscriptionRepository->createQueryBuilder('i')
                ->join('i.apprenant', 'a')->addSelect('a')
                ->join('i.cours', 'c')->addSelect('c')
                ->orderBy('c.titre', 'ASC')
                ->addOrderBy('a.nom', 'ASC')
                ->getQuery()
                ->toIterable();

            foreach ($inscriptions as $inscription) {
                $apprenant = $inscription->getApprenant();
                $cours = $inscription->getCours();
                $bilan = $progression->calculer($apprenant, $cours);
                $certificat = $certificatRepository->trouverPour($apprenant, $cours);

                fputcsv($sortie, array_map($this->celluleSure(...), [
                    $apprenant->getPrenom(),
                    $apprenant->getNom(),
                    $apprenant->getEmail(),
                    $cours->getTitre(),
                    $inscription->getCreatedAt()?->format('d/m/Y'),
                    $bilan->getPourcentage(),
                    $inscription->getTermineeAt()?->format('d/m/Y'),
                    $bilan->examen['meilleur'] ?? '',
                    $certificat?->getNumero(),
                    null === $certificat ? '' : ($certificat->estValide() ? 'valide' : 'révoqué'),
                ]), ';', '"', '');
            }

            fclose($sortie);
        });

        $reponse->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $reponse->headers->set('Content-Disposition', $reponse->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('elearning-suivi-%s.csv', (new \DateTimeImmutable())->format('Y-m-d')),
        ));

        return $reponse;
    }

    /** Neutralise les cellules interprétables comme formules par Excel (injection CSV). */
    private function celluleSure(mixed $valeur): string
    {
        $texte = (string) ($valeur ?? '');

        return '' !== $texte && \in_array($texte[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'".$texte : $texte;
    }
}
