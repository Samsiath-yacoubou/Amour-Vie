<?php

declare(strict_types=1);

namespace App\Controller\Apprendre;

use App\Elearning\CorrecteurQuiz;
use App\Elearning\GestionCertificats;
use App\Elearning\ProgressionCours;
use App\Entity\Apprenant;
use App\Entity\Cours;
use App\Entity\InscriptionCours;
use App\Entity\Lecon;
use App\Entity\LeconTerminee;
use App\Entity\ModuleCours;
use App\Entity\TentativeExamen;
use App\Entity\TentativeQuiz;
use App\Repository\CertificatRepository;
use App\Repository\CoursRepository;
use App\Repository\InscriptionCoursRepository;
use App\Repository\LeconRepository;
use App\Repository\LeconTermineeRepository;
use App\Repository\ModuleCoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Parcours apprenant de l'e-learning : catalogue, fiche cours, leçons,
 * quiz de module et espace « Mes cours ».
 *
 * Le catalogue et les fiches cours sont publics (découverte, référencement) ;
 * le reste exige un compte apprenant (cf. access_control).
 */
#[Route('/apprendre')]
class ApprendreController extends AbstractController
{
    public function __construct(
        private readonly CoursRepository $coursRepository,
        private readonly InscriptionCoursRepository $inscriptionRepository,
        private readonly ProgressionCours $progression,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'apprendre_catalogue', methods: ['GET'])]
    public function catalogue(): Response
    {
        $cours = $this->coursRepository->trouverPublies();

        // Pour un apprenant connecté : progression affichée sur chaque carte.
        $progressions = [];
        $apprenant = $this->apprenant();
        if (null !== $apprenant) {
            foreach ($this->inscriptionRepository->trouverPourApprenant($apprenant) as $inscription) {
                $progressions[$inscription->getCours()->getId()] = $this->progression->calculer($apprenant, $inscription->getCours());
            }
        }

        return $this->render('apprendre/catalogue.html.twig', [
            'cours' => $cours,
            'progressions' => $progressions,
        ]);
    }

    #[Route('/cours/{slug}', name: 'apprendre_cours', methods: ['GET'])]
    public function cours(string $slug, CertificatRepository $certificatRepository): Response
    {
        $cours = $this->coursPublie($slug);
        $apprenant = $this->apprenant();
        $inscription = null !== $apprenant ? $this->inscriptionRepository->trouver($apprenant, $cours) : null;

        return $this->render('apprendre/cours.html.twig', [
            'cours' => $cours,
            'inscription' => $inscription,
            'bilan' => null !== $inscription ? $this->progression->calculer($apprenant, $cours) : null,
            'certificat' => null !== $inscription ? $certificatRepository->trouverPour($apprenant, $cours) : null,
        ]);
    }

    #[Route('/cours/{slug}/commencer', name: 'apprendre_cours_commencer', methods: ['POST'])]
    #[IsGranted('ROLE_APPRENANT')]
    public function commencer(string $slug, Request $request): RedirectResponse
    {
        $cours = $this->coursPublie($slug);
        $apprenant = $this->apprenant();

        if (!$this->isCsrfTokenValid('commencer-'.$cours->getId(), (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('apprendre_cours', ['slug' => $slug]);
        }

        $inscription = $this->inscriptionRepository->trouver($apprenant, $cours);
        if (null === $inscription) {
            $inscription = new InscriptionCours($apprenant, $cours);
            $this->entityManager->persist($inscription);
            $this->entityManager->flush();
        }

        return $this->redirectVersReprise($inscription);
    }

    #[Route('/cours/{slug}/lecon/{id}', name: 'apprendre_lecon', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function lecon(string $slug, int $id, Request $request, LeconRepository $leconRepository): Response
    {
        $cours = $this->coursPublie($slug);
        $lecon = $this->leconDuCours($leconRepository, $id, $cours);
        $inscription = $this->inscriptionRequise($cours);
        if ($inscription instanceof Response) {
            return $inscription;
        }

        // Un préchargement au survol (navigation instantanée) ne doit pas
        // déplacer le point de reprise : seul un affichage réel le fait.
        if (!$request->headers->has('X-Prechargement') && $inscription->getDerniereLecon() !== $lecon) {
            $inscription->setDerniereLecon($lecon);
            $this->entityManager->flush();
        }

        $bilan = $this->progression->calculer($this->apprenant(), $cours);

        return $this->render('apprendre/lecon.html.twig', [
            'cours' => $cours,
            'lecon' => $lecon,
            'module' => $lecon->getModule(),
            'bilan' => $bilan,
            'terminee' => $bilan->leconEstTerminee($lecon),
            'voisines' => $this->progression->voisines($lecon),
        ]);
    }

    #[Route('/cours/{slug}/lecon/{id}/terminer', name: 'apprendre_lecon_terminer', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function terminerLecon(
        string $slug,
        int $id,
        Request $request,
        LeconRepository $leconRepository,
        LeconTermineeRepository $leconTermineeRepository,
    ): Response {
        $cours = $this->coursPublie($slug);
        $lecon = $this->leconDuCours($leconRepository, $id, $cours);
        $inscription = $this->inscriptionRequise($cours);
        if ($inscription instanceof Response) {
            return $inscription;
        }

        if (!$this->isCsrfTokenValid('terminer-lecon-'.$lecon->getId(), (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('apprendre_lecon', ['slug' => $slug, 'id' => $id]);
        }

        $apprenant = $this->apprenant();
        if (null === $leconTermineeRepository->findOneBy(['apprenant' => $apprenant, 'lecon' => $lecon])) {
            $this->entityManager->persist(new LeconTerminee($apprenant, $lecon));
            $this->entityManager->flush();
        }

        if ($this->progression->actualiserInscription($inscription)) {
            $this->entityManager->flush();
            $this->addFlash('success', sprintf('Bravo, tu as terminé le cours « %s » !', $cours->getTitre()));

            return $this->redirectToRoute('apprendre_cours', ['slug' => $slug]);
        }

        // Fin des leçons du module : place au quiz s'il n'est pas encore réussi.
        $module = $lecon->getModule();
        $derniereDuModule = $module->getLecons()->last() === $lecon;
        if ($derniereDuModule && $module->aUnQuiz()) {
            $bilan = $this->progression->calculer($apprenant, $cours);
            foreach ($bilan->modules as $etat) {
                if ($etat['module'] === $module && !($etat['quiz']['reussi'] ?? true)) {
                    return $this->redirectToRoute('apprendre_quiz', ['slug' => $slug, 'id' => $module->getId()]);
                }
            }
        }

        $suivante = $this->progression->voisines($lecon)['suivante'];
        if (null !== $suivante) {
            return $this->redirectToRoute('apprendre_lecon', ['slug' => $slug, 'id' => $suivante->getId()]);
        }

        return $this->redirectVersReprise($inscription);
    }

    #[Route('/cours/{slug}/module/{id}/quiz', name: 'apprendre_quiz', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function quiz(string $slug, int $id, ModuleCoursRepository $moduleRepository, CorrecteurQuiz $correcteur): Response
    {
        $cours = $this->coursPublie($slug);
        $module = $this->moduleDuCours($moduleRepository, $id, $cours);
        $inscription = $this->inscriptionRequise($cours);
        if ($inscription instanceof Response) {
            return $inscription;
        }

        return $this->render('apprendre/quiz.html.twig', [
            'cours' => $cours,
            'module' => $module,
            'questions' => $correcteur->questionsSansReponses($module->getQuiz()),
            'bilan' => $this->progression->calculer($this->apprenant(), $cours),
        ]);
    }

    #[Route('/cours/{slug}/module/{id}/quiz/corriger', name: 'apprendre_quiz_corriger', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function corrigerQuiz(
        string $slug,
        int $id,
        Request $request,
        ModuleCoursRepository $moduleRepository,
        CorrecteurQuiz $correcteur,
    ): JsonResponse {
        $cours = $this->coursPublie($slug);
        $module = $this->moduleDuCours($moduleRepository, $id, $cours);
        $inscription = $this->inscriptionRequise($cours);
        if ($inscription instanceof Response) {
            return $this->json(['erreur' => 'Commence le cours avant de passer ce quiz.'], Response::HTTP_FORBIDDEN);
        }

        if (!$this->isCsrfTokenValid('quiz-module-'.$module->getId(), (string) $request->headers->get('X-CSRF-Token'))) {
            return $this->json(['erreur' => 'Ta session a expiré. Recharge la page et réessaie.'], Response::HTTP_FORBIDDEN);
        }

        $donnees = json_decode($request->getContent(), true);
        $reponses = \is_array($donnees['reponses'] ?? null) ? $donnees['reponses'] : [];

        $resultat = $correcteur->corriger($module->getQuiz(), $reponses, $cours->getSeuilReussite());

        $this->entityManager->persist(new TentativeQuiz(
            $this->apprenant(),
            $module,
            $resultat['score'],
            $resultat['total'],
            $resultat['reussi'],
        ));
        $this->entityManager->flush();

        $coursTermine = $this->progression->actualiserInscription($inscription);
        $this->entityManager->flush();

        return $this->json($resultat + [
            'coursTermine' => $coursTermine,
            'suite' => $this->urlReprise($inscription),
        ]);
    }

    #[Route('/cours/{slug}/examen', name: 'apprendre_examen', methods: ['GET'])]
    public function examen(string $slug, CorrecteurQuiz $correcteur): Response
    {
        $cours = $this->coursPublie($slug);
        $inscription = $this->inscriptionRequise($cours);
        if ($inscription instanceof Response) {
            return $inscription;
        }

        $bilan = $this->progression->calculer($this->apprenant(), $cours);
        if (null === $bilan->examen || !$bilan->examen['accessible']) {
            return $this->redirectToRoute('apprendre_cours', ['slug' => $slug]);
        }

        return $this->render('apprendre/examen.html.twig', [
            'cours' => $cours,
            'questions' => $correcteur->questionsSansReponses($cours->getExamenFinal()),
            'bilan' => $bilan,
        ]);
    }

    #[Route('/cours/{slug}/examen/corriger', name: 'apprendre_examen_corriger', methods: ['POST'])]
    public function corrigerExamen(string $slug, Request $request, CorrecteurQuiz $correcteur): JsonResponse
    {
        $cours = $this->coursPublie($slug);
        $inscription = $this->inscriptionRequise($cours);
        if ($inscription instanceof Response) {
            return $this->json(['erreur' => 'Commence le cours avant de passer l\'examen.'], Response::HTTP_FORBIDDEN);
        }

        if (!$this->isCsrfTokenValid('examen-'.$cours->getId(), (string) $request->headers->get('X-CSRF-Token'))) {
            return $this->json(['erreur' => 'Ta session a expiré. Recharge la page et réessaie.'], Response::HTTP_FORBIDDEN);
        }

        $apprenant = $this->apprenant();
        $bilan = $this->progression->calculer($apprenant, $cours);
        if (null === $bilan->examen || !$bilan->examen['accessible']) {
            return $this->json(['erreur' => 'L\'examen n\'est pas accessible : valide d\'abord tous les modules, dans la limite des tentatives.'], Response::HTTP_FORBIDDEN);
        }

        $donnees = json_decode($request->getContent(), true);
        $reponses = \is_array($donnees['reponses'] ?? null) ? $donnees['reponses'] : [];
        $resultat = $correcteur->corriger($cours->getExamenFinal(), $reponses, $cours->getSeuilReussite());

        $this->entityManager->persist(new TentativeExamen($apprenant, $cours, $resultat['score'], $resultat['total'], $resultat['reussi']));
        $this->entityManager->flush();

        $coursTermine = $this->progression->actualiserInscription($inscription);
        $this->entityManager->flush();

        $restantes = $this->progression->calculer($apprenant, $cours)->examen['restantes'];

        // Tant qu'il reste des tentatives, on ne dévoile pas les bonnes réponses :
        // seulement quelles questions sont justes ou fausses.
        if (!$resultat['reussi'] && 0 !== $restantes) {
            foreach ($resultat['corrections'] as $id => $correction) {
                $resultat['corrections'][$id] = ['choixCorrects' => [], 'juste' => $correction['juste'], 'explication' => null];
            }
        }

        return $this->json($resultat + [
            'coursTermine' => $coursTermine,
            'tentativesRestantes' => $restantes,
            'suite' => $coursTermine
                ? $this->generateUrl('apprendre_certificat_obtenir', ['slug' => $slug])
                : $this->generateUrl('apprendre_cours', ['slug' => $slug]),
        ]);
    }

    /** Confirmation de l'orthographe du nom, puis émission automatique du certificat. */
    #[Route('/cours/{slug}/certificat', name: 'apprendre_certificat_obtenir', methods: ['GET', 'POST'])]
    public function obtenirCertificat(string $slug, Request $request, GestionCertificats $certificats, CertificatRepository $certificatRepository): Response
    {
        $cours = $this->coursPublie($slug);
        $apprenant = $this->apprenant();

        $existant = $certificatRepository->trouverPour($apprenant, $cours);
        if (null !== $existant) {
            return $this->redirectToRoute('apprendre_mes_certificats');
        }

        if (!$certificats->estEligible($apprenant, $cours)) {
            $this->addFlash('info', 'Termine le cours (modules et examen final) pour obtenir ton certificat.');

            return $this->redirectToRoute('apprendre_cours', ['slug' => $slug]);
        }

        $erreurs = [];
        $prenom = (string) $request->request->get('prenom', $apprenant->getPrenom());
        $nom = (string) $request->request->get('nom', $apprenant->getNom());

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('certificat-'.$cours->getId(), (string) $request->request->get('_token'))) {
                $erreurs[] = 'La session a expiré, merci de réessayer.';
            }
            $prenom = trim($prenom);
            $nom = trim($nom);
            if ('' === $prenom || '' === $nom || mb_strlen($prenom) > 100 || mb_strlen($nom) > 100) {
                $erreurs[] = 'Indique ton prénom et ton nom (100 caractères maximum chacun).';
            }
            if (!$request->request->getBoolean('confirmation')) {
                $erreurs[] = 'Confirme que ton nom est bien orthographié : il ne pourra plus être modifié par toi ensuite.';
            }

            if ([] === $erreurs) {
                $certificat = $certificats->emettre($apprenant, $cours, $prenom, $nom);
                $this->addFlash('success', sprintf('Félicitations ! Ton certificat n° %s est prêt.', $certificat->getNumero()));

                return $this->redirectToRoute('apprendre_mes_certificats');
            }
        }

        return $this->render('apprendre/certificat_obtenir.html.twig', [
            'cours' => $cours,
            'prenom' => $prenom,
            'nom' => $nom,
            'erreurs' => $erreurs,
        ]);
    }

    #[Route('/mes-certificats', name: 'apprendre_mes_certificats', methods: ['GET'])]
    public function mesCertificats(CertificatRepository $certificatRepository, GestionCertificats $certificats): Response
    {
        $liste = [];
        foreach ($certificatRepository->trouverPourApprenant($this->apprenant()) as $certificat) {
            $liste[] = ['certificat' => $certificat, 'urlVerification' => $certificats->urlVerification($certificat)];
        }

        return $this->render('apprendre/mes_certificats.html.twig', [
            'certificats' => $liste,
        ]);
    }

    #[Route('/mes-certificats/{numero}.pdf', name: 'apprendre_certificat_pdf', methods: ['GET'])]
    public function telechargerCertificat(string $numero, CertificatRepository $certificatRepository, GestionCertificats $certificats): BinaryFileResponse
    {
        $certificat = $certificatRepository->trouverParNumero($numero);
        if (null === $certificat || $certificat->getApprenant() !== $this->apprenant()) {
            throw $this->createNotFoundException('Ce certificat n\'existe pas.');
        }

        return $this->file($certificats->cheminPdf($certificat), $certificats->nomFichier($certificat), ResponseHeaderBag::DISPOSITION_INLINE);
    }

    #[Route('/mon-espace', name: 'apprendre_mon_espace', methods: ['GET'])]
    public function monEspace(CertificatRepository $certificatRepository): Response
    {
        $apprenant = $this->apprenant();

        $certificatsParCours = [];
        foreach ($certificatRepository->trouverPourApprenant($apprenant) as $certificat) {
            if (null !== $certificat->getCours()) {
                $certificatsParCours[$certificat->getCours()->getId()] = $certificat;
            }
        }

        $suivis = [];
        foreach ($this->inscriptionRepository->trouverPourApprenant($apprenant) as $inscription) {
            $suivis[] = [
                'inscription' => $inscription,
                'bilan' => $this->progression->calculer($apprenant, $inscription->getCours()),
                'reprise' => $this->urlReprise($inscription),
                'certificat' => $certificatsParCours[$inscription->getCours()->getId()] ?? null,
            ];
        }

        return $this->render('apprendre/mon_espace.html.twig', [
            'apprenant' => $apprenant,
            'suivis' => $suivis,
        ]);
    }

    private function apprenant(): ?Apprenant
    {
        $utilisateur = $this->getUser();

        return $utilisateur instanceof Apprenant ? $utilisateur : null;
    }

    private function coursPublie(string $slug): Cours
    {
        return $this->coursRepository->trouverPublieParSlug($slug)
            ?? throw $this->createNotFoundException('Ce cours n\'existe pas ou n\'est pas encore publié.');
    }

    private function leconDuCours(LeconRepository $repository, int $id, Cours $cours): Lecon
    {
        $lecon = $repository->find($id);
        if (null === $lecon || $lecon->getCours() !== $cours) {
            throw $this->createNotFoundException('Cette leçon n\'existe pas.');
        }

        return $lecon;
    }

    private function moduleDuCours(ModuleCoursRepository $repository, int $id, Cours $cours): ModuleCours
    {
        $module = $repository->find($id);
        if (null === $module || $module->getCours() !== $cours || !$module->aUnQuiz()) {
            throw $this->createNotFoundException('Ce quiz n\'existe pas.');
        }

        return $module;
    }

    /** Les leçons et quiz ne s'ouvrent qu'après avoir commencé le cours. */
    private function inscriptionRequise(Cours $cours): InscriptionCours|Response
    {
        $inscription = $this->inscriptionRepository->trouver($this->apprenant(), $cours);
        if (null === $inscription) {
            $this->addFlash('info', 'Commence le cours pour accéder à ses leçons.');

            return $this->redirectToRoute('apprendre_cours', ['slug' => $cours->getSlug()]);
        }

        return $inscription;
    }

    /**
     * Où « Reprendre » emmène le jeune : la prochaine étape non faite, sinon
     * la fiche du cours (cours terminé).
     */
    private function urlReprise(InscriptionCours $inscription): string
    {
        $cours = $inscription->getCours();
        $etape = $this->progression->calculer($inscription->getApprenant(), $cours)->prochaineEtape;

        return match ($etape['type'] ?? null) {
            'lecon' => $this->generateUrl('apprendre_lecon', ['slug' => $cours->getSlug(), 'id' => $etape['lecon']->getId()]),
            'quiz' => $this->generateUrl('apprendre_quiz', ['slug' => $cours->getSlug(), 'id' => $etape['module']->getId()]),
            'examen' => $this->generateUrl('apprendre_examen', ['slug' => $cours->getSlug()]),
            default => $this->generateUrl('apprendre_cours', ['slug' => $cours->getSlug()]),
        };
    }

    private function redirectVersReprise(InscriptionCours $inscription): RedirectResponse
    {
        return $this->redirect($this->urlReprise($inscription));
    }
}
