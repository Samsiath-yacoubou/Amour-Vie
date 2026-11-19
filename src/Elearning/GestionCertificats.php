<?php

declare(strict_types=1);

namespace App\Elearning;

use App\Entity\Apprenant;
use App\Entity\Certificat;
use App\Entity\Cours;
use App\Repository\CertificatRepository;
use App\Repository\InscriptionCoursRepository;
use App\Repository\TentativeExamenRepository;
use App\Service\SiteContexte;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * Émission, stockage et gestion des certificats de l'e-learning.
 *
 * - Émission automatique : dès qu'un cours est terminé (modules validés et
 *   examen final réussi), après confirmation du nom par l'apprenant.
 * - Un seul certificat par apprenant et par cours.
 * - Numéro aléatoire non devinable, vérifiable publiquement (QR code).
 * - PDF généré côté serveur et conservé hors du dossier public ; regénéré
 *   à l'identique s'il venait à manquer.
 */
final class GestionCertificats
{
    /** Alphabet sans caractères ambigus (0/O, 1/I/L) : 31 symboles, ≈ 59 bits pour 12 caractères. */
    private const ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CertificatRepository $certificatRepository,
        private readonly InscriptionCoursRepository $inscriptionRepository,
        private readonly TentativeExamenRepository $tentativeExamenRepository,
        private readonly ProgressionCours $progression,
        private readonly SiteContexte $site,
        private readonly StorageInterface $stockageFichiers,
        private readonly Environment $twig,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LoggerInterface $logger,
        #[Autowire('%kernel.project_dir%/var/certificats')]
        private readonly string $dossierCertificats,
    ) {
    }

    /** Le cours est-il terminé par l'apprenant (condition de délivrance) ? */
    public function estEligible(Apprenant $apprenant, Cours $cours): bool
    {
        $inscription = $this->inscriptionRepository->trouver($apprenant, $cours);

        return null !== $inscription && $this->progression->calculer($apprenant, $cours)->estTermine();
    }

    /**
     * Délivre le certificat, après que l'apprenant a confirmé l'orthographe
     * de son nom (le compte est mis à jour avec ce nom).
     */
    public function emettre(Apprenant $apprenant, Cours $cours, string $prenom, string $nom): Certificat
    {
        $existant = $this->certificatRepository->trouverPour($apprenant, $cours);
        if (null !== $existant) {
            return $existant;
        }

        if (!$this->estEligible($apprenant, $cours)) {
            throw new \LogicException('Le cours n\'est pas encore terminé : aucun certificat ne peut être délivré.');
        }

        $apprenant->setPrenom($prenom)->setNom($nom);

        $certificat = new Certificat(
            $this->nouveauNumero(),
            $apprenant,
            $cours,
            $cours->aUnExamenFinal() ? $this->tentativeExamenRepository->meilleurPourcentage($apprenant, $cours) : null,
        );

        $this->entityManager->persist($certificat);
        $this->entityManager->flush();

        // Le certificat est délivré même si le PDF échoue ici : il sera
        // regénéré au premier téléchargement (cf. cheminPdf()).
        try {
            $this->genererPdf($certificat);
        } catch (\Throwable $e) {
            $this->logger->error('Génération du PDF du certificat {numero} impossible : {message}', [
                'numero' => $certificat->getNumero(),
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
        }

        return $certificat;
    }

    public function revoquer(Certificat $certificat, ?string $motif): void
    {
        $certificat->revoquer($motif);
        $this->entityManager->flush();
        $this->genererPdf($certificat);
    }

    public function reemettre(Certificat $certificat): void
    {
        $certificat->reemettre();
        $this->entityManager->flush();
        $this->genererPdf($certificat);
    }

    /** Chemin du PDF sur le disque, regénéré au besoin. */
    public function cheminPdf(Certificat $certificat): string
    {
        $chemin = $this->cheminFichier($certificat);
        if (!is_file($chemin)) {
            $this->genererPdf($certificat);
        }

        return $chemin;
    }

    public function nomFichier(Certificat $certificat): string
    {
        return sprintf('certificat-amour-vie-%s.pdf', strtolower($certificat->getNumero()));
    }

    public function urlVerification(Certificat $certificat): string
    {
        return $this->urlGenerator->generate('certificat_verifier', ['numero' => $certificat->getNumero()], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function genererPdf(Certificat $certificat): void
    {
        $parametres = $this->site->getParametres();

        $html = $this->twig->render('certificat/pdf.html.twig', [
            'certificat' => $certificat,
            'parametres' => $parametres,
            'logo' => $this->imageEnDataUri($this->stockageFichiers->resolvePath($parametres, 'logoFile')),
            'signature' => $this->imageEnDataUri($this->stockageFichiers->resolvePath($parametres, 'certificatSignatureFile')),
            'qrCode' => 'data:image/svg+xml;base64,'.base64_encode($this->qrCodeSvg($this->urlVerification($certificat))),
            'urlVerification' => $this->urlVerification($certificat),
        ]);

        $options = (new Options())
            ->setIsRemoteEnabled(false)
            ->setDefaultFont('Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        (new Filesystem())->dumpFile($this->cheminFichier($certificat), $dompdf->output());
    }

    private function cheminFichier(Certificat $certificat): string
    {
        return $this->dossierCertificats.'/'.$certificat->getNumero().'.pdf';
    }

    private function nouveauNumero(): string
    {
        do {
            $caracteres = '';
            for ($i = 0; $i < 12; ++$i) {
                $caracteres .= self::ALPHABET[random_int(0, \strlen(self::ALPHABET) - 1)];
            }
            $numero = 'AV-'.implode('-', str_split($caracteres, 4));
        } while (null !== $this->certificatRepository->trouverParNumero($numero));

        return $numero;
    }

    private function qrCodeSvg(string $contenu): string
    {
        return (new Writer(new ImageRenderer(new RendererStyle(240, 1), new SvgImageBackEnd())))->writeString($contenu);
    }

    /** Les images sont intégrées au PDF (dompdf n'accède à aucun fichier ni à internet). */
    private function imageEnDataUri(?string $chemin): ?string
    {
        if (null === $chemin || !is_file($chemin)) {
            return null;
        }

        $type = mime_content_type($chemin) ?: 'image/png';

        return sprintf('data:%s;base64,%s', $type, base64_encode((string) file_get_contents($chemin)));
    }
}
