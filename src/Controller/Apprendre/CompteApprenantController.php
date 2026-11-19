<?php

declare(strict_types=1);

namespace App\Controller\Apprendre;

use App\Entity\Apprenant;
use App\Form\InscriptionApprenantType;
use App\Form\NouveauMotDePasseType;
use App\Repository\ApprenantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Comptes des apprenants de l'e-learning : inscription, connexion et
 * mot de passe oublié. Totalement séparé de l'authentification du
 * back-office (pare-feu « apprendre », cf. security.yaml).
 */
#[Route('/apprendre')]
class CompteApprenantController extends AbstractController
{
    /** Durée de validité d'un lien de réinitialisation. */
    private const VALIDITE_JETON = '+1 hour';

    /** Délai minimal entre deux e-mails de réinitialisation pour un même compte. */
    private const DELAI_ENTRE_ENVOIS = 300;

    public function __construct(
        #[Autowire('%env(MAILER_EXPEDITEUR)%')]
        private readonly string $expediteur,
    ) {
    }

    #[Route('/inscription', name: 'apprendre_inscription', methods: ['GET', 'POST'])]
    public function inscription(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $hasher,
        Security $security,
    ): Response {
        if ($this->getUser() instanceof Apprenant) {
            return $this->redirectToRoute('apprendre_mon_espace');
        }

        $apprenant = new Apprenant();
        $formulaire = $this->createForm(InscriptionApprenantType::class, $apprenant);
        $formulaire->handleRequest($request);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            // Robot détecté par le champ leurre : on fait comme si de rien n'était.
            if ('' !== trim((string) $formulaire->get('siteWeb')->getData())) {
                return $this->redirectToRoute('apprendre_catalogue');
            }

            $apprenant
                ->setPassword($hasher->hashPassword($apprenant, $formulaire->get('motDePasse')->getData()))
                ->setConsentementAt(new \DateTimeImmutable());

            $entityManager->persist($apprenant);
            $entityManager->flush();

            $security->login($apprenant, 'form_login', 'apprendre');
            $this->addFlash('success', sprintf('Bienvenue %s ! Ton compte est créé.', $apprenant->getPrenom()));

            return $this->redirect($this->cibleSure($request->query->get('cible')) ?? $this->generateUrl('apprendre_catalogue'));
        }

        return $this->render('apprendre/compte/inscription.html.twig', [
            'formulaire' => $formulaire,
            'cible' => $this->cibleSure($request->query->get('cible')),
        ]);
    }

    #[Route('/connexion', name: 'apprendre_connexion', methods: ['GET', 'POST'])]
    public function connexion(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser() instanceof Apprenant) {
            return $this->redirectToRoute('apprendre_mon_espace');
        }

        return $this->render('apprendre/compte/connexion.html.twig', [
            'derniereSaisie' => $authenticationUtils->getLastUsername(),
            'erreur' => $authenticationUtils->getLastAuthenticationError(),
            'cible' => $this->cibleSure($request->query->get('cible')),
        ]);
    }

    #[Route('/deconnexion', name: 'apprendre_deconnexion', methods: ['GET'])]
    public function deconnexion(): never
    {
        // Interceptée par le pare-feu « apprendre » : ce corps n'est jamais exécuté.
        throw new \LogicException('La déconnexion est gérée par le pare-feu de sécurité.');
    }

    #[Route('/mot-de-passe-oublie', name: 'apprendre_mot_de_passe_oublie', methods: ['GET', 'POST'])]
    public function motDePasseOublie(
        Request $request,
        ApprenantRepository $apprenantRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
    ): Response {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('mot-de-passe-oublie', (string) $request->request->get('_token'))) {
                $this->addFlash('warning', 'La session a expiré, merci de réessayer.');

                return $this->redirectToRoute('apprendre_mot_de_passe_oublie');
            }

            $apprenant = $apprenantRepository->trouverParEmail((string) $request->request->get('email'));

            if (null !== $apprenant && $apprenant->isActif() && !$this->envoiRecent($apprenant)) {
                $jeton = bin2hex(random_bytes(32));
                $apprenant->definirJetonReinitialisation(hash('sha256', $jeton), new \DateTimeImmutable(self::VALIDITE_JETON));
                $entityManager->flush();

                try {
                    $mailer->send((new TemplatedEmail())
                        ->from(Address::create($this->expediteur))
                        ->to(new Address((string) $apprenant->getEmail(), $apprenant->getNomComplet()))
                        ->subject('Choisis un nouveau mot de passe · Amour&Vie')
                        ->htmlTemplate('apprendre/email/reinitialisation.html.twig')
                        ->context([
                            'apprenant' => $apprenant,
                            'lien' => $this->generateUrl('apprendre_reinitialiser', ['jeton' => $jeton], UrlGeneratorInterface::ABSOLUTE_URL),
                        ]));
                } catch (TransportExceptionInterface) {
                    $this->addFlash('error', 'L\'e-mail n\'a pas pu être envoyé. Réessaie dans quelques minutes.');

                    return $this->redirectToRoute('apprendre_mot_de_passe_oublie');
                }
            }

            // Même réponse que le compte existe ou non : on ne révèle pas qui est inscrit.
            $this->addFlash('success', 'Si un compte existe avec cette adresse, un e-mail vient de t\'être envoyé. Le lien est valable une heure.');

            return $this->redirectToRoute('apprendre_connexion');
        }

        return $this->render('apprendre/compte/mot_de_passe_oublie.html.twig');
    }

    #[Route('/mot-de-passe/{jeton}', name: 'apprendre_reinitialiser', requirements: ['jeton' => '[a-f0-9]{64}'], methods: ['GET', 'POST'])]
    public function reinitialiser(
        string $jeton,
        Request $request,
        ApprenantRepository $apprenantRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $hasher,
    ): Response {
        $apprenant = $apprenantRepository->trouverParJetonValide($jeton);

        if (null === $apprenant) {
            $this->addFlash('warning', 'Ce lien n\'est plus valable. Demande un nouvel e-mail de réinitialisation.');

            return $this->redirectToRoute('apprendre_mot_de_passe_oublie');
        }

        $formulaire = $this->createForm(NouveauMotDePasseType::class);
        $formulaire->handleRequest($request);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $apprenant
                ->setPassword($hasher->hashPassword($apprenant, $formulaire->get('motDePasse')->getData()))
                ->definirJetonReinitialisation(null, null);
            $entityManager->flush();

            $this->addFlash('success', 'Ton mot de passe a été modifié. Tu peux te connecter.');

            return $this->redirectToRoute('apprendre_connexion');
        }

        return $this->render('apprendre/compte/reinitialiser.html.twig', [
            'formulaire' => $formulaire,
        ]);
    }

    private function envoiRecent(Apprenant $apprenant): bool
    {
        $expiration = $apprenant->getJetonExpireAt();
        if (null === $expiration) {
            return false;
        }

        $emisLe = $expiration->modify('-1 hour');

        return (new \DateTimeImmutable())->getTimestamp() - $emisLe->getTimestamp() < self::DELAI_ENTRE_ENVOIS;
    }

    /** N'accepte qu'un chemin interne à l'e-learning, pour éviter toute redirection ouverte. */
    private function cibleSure(?string $cible): ?string
    {
        if (null === $cible || !str_starts_with($cible, '/apprendre/') || str_starts_with($cible, '//') || str_contains($cible, '\\')) {
            return null;
        }

        return $cible;
    }
}
