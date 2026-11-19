<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CertificatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Vérification publique d'un certificat de l'e-learning, sans compte :
 * par saisie du numéro ou en scannant le QR code imprimé sur le certificat.
 *
 * On n'affiche que ce que porte déjà le certificat (nom, cours, date) : la
 * page ne révèle rien de plus sur le jeune.
 */
class VerificationCertificatController extends AbstractController
{
    #[Route('/verifier', name: 'certificat_verification', methods: ['GET'])]
    public function formulaire(Request $request): Response
    {
        $saisie = $this->normaliser((string) $request->query->get('numero'));
        if ('' !== $saisie) {
            return $this->redirectToRoute('certificat_verifier', ['numero' => $saisie]);
        }

        return $this->render('certificat/verification.html.twig', [
            'numero' => null,
            'certificat' => null,
        ]);
    }

    #[Route('/verifier/{numero}', name: 'certificat_verifier', requirements: ['numero' => '[A-Za-z0-9-]{1,30}'], methods: ['GET'])]
    public function verifier(string $numero, CertificatRepository $certificatRepository): Response
    {
        $numero = $this->normaliser($numero);
        $certificat = $certificatRepository->trouverParNumero($numero);

        $reponse = $this->render('certificat/verification.html.twig', [
            'numero' => $numero,
            'certificat' => $certificat,
        ]);

        if (null === $certificat) {
            $reponse->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        return $reponse;
    }

    /** Tolère les minuscules, les espaces et l'absence de tirets à la saisie. */
    private function normaliser(string $saisie): string
    {
        $brut = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $saisie) ?? '');
        if (str_starts_with($brut, 'AV') && 14 === \strlen($brut)) {
            return 'AV-'.implode('-', str_split(substr($brut, 2), 4));
        }

        return $brut;
    }
}
