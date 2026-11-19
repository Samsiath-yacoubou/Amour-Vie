<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\MessageContact;
use App\Entity\SectionPage;
use App\Form\ContactType;
use App\Repository\ArticleRepository;
use App\Repository\ChiffreCleRepository;
use App\Repository\MembreRepository;
use App\Repository\MessageContactRepository;
use App\Repository\PartenaireRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\PublicationRepository;
use App\Repository\SectionPageRepository;
use App\Repository\ValeurRepository;
use App\Service\SiteContexte;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Pages institutionnelles : présentation, contact, recherche, pages légales.
 */
class PageController extends AbstractController
{
    /** Nombre maximal de messages acceptés par IP sur une heure glissante. */
    private const LIMITE_MESSAGES_PAR_HEURE = 5;

    #[Route('/qui-sommes-nous', name: 'qui_sommes_nous', methods: ['GET'])]
    public function quiSommesNous(
        SectionPageRepository $sectionPageRepository,
        ValeurRepository $valeurRepository,
        MembreRepository $membreRepository,
        ChiffreCleRepository $chiffreCleRepository,
        PartenaireRepository $partenaireRepository,
    ): Response {
        return $this->render('page/qui_sommes_nous.html.twig', [
            'sections' => $sectionPageRepository->trouverParPage(SectionPage::PAGE_QUI_SOMMES_NOUS),
            'valeurs' => $valeurRepository->trouverActives(),
            'equipes' => $membreRepository->trouverGroupesParCategorie(),
            'chiffres' => $chiffreCleRepository->trouverActifs(),
            'partenaires' => $partenaireRepository->trouverActifs(),
        ]);
    }

    #[Route('/contact', name: 'contact', methods: ['GET', 'POST'])]
    public function contact(
        Request $request,
        EntityManagerInterface $entityManager,
        MessageContactRepository $messageContactRepository,
        MailerInterface $mailer,
        SiteContexte $siteContexte,
        SectionPageRepository $sectionPageRepository,
    ): Response {
        $message = new MessageContact();
        $formulaire = $this->createForm(ContactType::class, $message);
        $formulaire->handleRequest($request);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $ip = $request->getClientIp();

            // Le pot de miel n'est jamais rempli par un visiteur légitime :
            // on répond comme si tout allait bien pour ne pas informer le robot.
            $estSpam = '' !== trim((string) $formulaire->get('siteWeb')->getData());

            $tropDeMessages = null !== $ip && $messageContactRepository->compterDepuisIp(
                $ip,
                new \DateTimeImmutable('-1 hour'),
            ) >= self::LIMITE_MESSAGES_PAR_HEURE;

            if ($tropDeMessages) {
                $this->addFlash('warning', 'Vous avez envoyé plusieurs messages récemment. Merci de patienter avant d\'en envoyer un nouveau.');

                return $this->redirectToRoute('contact');
            }

            if (!$estSpam) {
                $message->setAdresseIp($ip);
                $entityManager->persist($message);
                $entityManager->flush();

                $this->notifierEquipe($mailer, $siteContexte, $message);
            }

            $this->addFlash('success', 'Votre message a bien été envoyé. L\'équipe Amour&Vie vous répondra dans les meilleurs délais.');

            return $this->redirectToRoute('contact');
        }

        return $this->render('page/contact.html.twig', [
            'formulaire' => $formulaire,
            'sections' => $sectionPageRepository->trouverParPage(SectionPage::PAGE_CONTACT),
        ]);
    }

    /**
     * Recherche globale sur les actualités, publications et programmes (§ 2.1).
     */
    #[Route('/recherche', name: 'recherche', methods: ['GET'])]
    public function recherche(
        Request $request,
        ArticleRepository $articleRepository,
        PublicationRepository $publicationRepository,
        ProgrammeRepository $programmeRepository,
    ): Response {
        $terme = trim((string) $request->query->get('q'));

        if ('' === $terme) {
            return $this->render('page/recherche.html.twig', [
                'terme' => '',
                'articles' => [],
                'publications' => [],
                'programmes' => [],
                'total' => 0,
            ]);
        }

        $articles = iterator_to_array($articleRepository->rechercherPublies(['recherche' => $terme], 1, 12));
        $publications = iterator_to_array($publicationRepository->rechercherPubliees(['recherche' => $terme], 1, 12));

        // Les programmes sont peu nombreux : un filtrage en mémoire suffit.
        $termeMinuscule = mb_strtolower($terme);
        $programmes = array_values(array_filter(
            $programmeRepository->trouverActifs(),
            static fn ($programme) => str_contains(mb_strtolower($programme->getNom().' '.$programme->getSigle().' '.$programme->getDescriptionCourte()), $termeMinuscule),
        ));

        return $this->render('page/recherche.html.twig', [
            'terme' => $terme,
            'articles' => $articles,
            'publications' => $publications,
            'programmes' => $programmes,
            'total' => \count($articles) + \count($publications) + \count($programmes),
        ]);
    }

    /**
     * Page hub « Espace jeunes » : questions anonymes, FAQ, quiz, témoignages.
     */
    #[Route('/espace-jeunes', name: 'espace_jeunes', methods: ['GET'])]
    public function espaceJeunes(SectionPageRepository $sectionPageRepository): Response
    {
        return $this->render('espace_jeunes/index.html.twig', [
            'sections' => $sectionPageRepository->trouverParPage(SectionPage::PAGE_ESPACE_JEUNES),
        ]);
    }

    /**
     * Page hub « Ressources » : publications, FAQ, quiz.
     */
    #[Route('/ressources', name: 'ressources', methods: ['GET'])]
    public function ressources(SectionPageRepository $sectionPageRepository): Response
    {
        return $this->render('ressources/index.html.twig', [
            'sections' => $sectionPageRepository->trouverParPage(SectionPage::PAGE_RESSOURCES),
        ]);
    }

    #[Route('/plan-du-site', name: 'plan_du_site', methods: ['GET'])]
    public function planDuSite(
        ProgrammeRepository $programmeRepository,
        ArticleRepository $articleRepository,
        PublicationRepository $publicationRepository,
    ): Response {
        return $this->render('page/plan_du_site.html.twig', [
            'programmes' => $programmeRepository->trouverActifs(),
            'articles' => $articleRepository->trouverRecents(20),
            'publications' => $publicationRepository->trouverRecentes(20),
        ]);
    }

    /**
     * Pages légales, dont le contenu est administré dans les paramètres (§ 3.12).
     */
    #[Route(
        '/{page}',
        name: 'page_legale',
        requirements: ['page' => 'mentions-legales|politique-de-confidentialite|conditions-utilisation'],
        methods: ['GET'],
        priority: -10,
    )]
    public function pageLegale(string $page, SiteContexte $siteContexte): Response
    {
        $parametres = $siteContexte->getParametres();

        [$titre, $contenu] = match ($page) {
            'mentions-legales' => ['Mentions légales', $parametres->getMentionsLegales()],
            'politique-de-confidentialite' => ['Politique de confidentialité', $parametres->getPolitiqueConfidentialite()],
            'conditions-utilisation' => ["Conditions d'utilisation", $parametres->getConditionsUtilisation()],
        };

        return $this->render('page/legale.html.twig', [
            'titre' => $titre,
            'contenu' => $contenu,
        ]);
    }

    /** Notifie l'équipe par e-mail, sans bloquer la réponse en cas d'échec SMTP. */
    private function notifierEquipe(MailerInterface $mailer, SiteContexte $siteContexte, MessageContact $message): void
    {
        $destinataire = $siteContexte->getParametres()->getEmail();

        if (null === $destinataire || '' === $destinataire) {
            return;
        }

        try {
            $mailer->send(
                (new Email())
                    ->from($destinataire)
                    ->to($destinataire)
                    ->replyTo($message->getEmail())
                    ->subject(sprintf('[Site Amour&Vie] %s', $message->getSujet()))
                    ->text(sprintf(
                        "Nouveau message reçu via le formulaire de contact.\n\n".
                        "De : %s <%s>\nSujet : %s\n\n%s\n",
                        $message->getNomComplet(),
                        $message->getEmail(),
                        $message->getSujet(),
                        $message->getMessage(),
                    ))
            );
        } catch (\Throwable) {
            // Le message est déjà enregistré en base : une panne SMTP ne doit
            // pas empêcher le visiteur de recevoir sa confirmation.
        }
    }
}
