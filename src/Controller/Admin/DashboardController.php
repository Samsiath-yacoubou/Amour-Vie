<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Utilisateur;
use App\Enum\StatutArticle;
use App\Repository\ArticleRepository;
use App\Repository\EvenementRepository;
use App\Repository\MembreRepository;
use App\Repository\MessageContactRepository;
use App\Repository\NewsletterAbonneRepository;
use App\Repository\PartenaireRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\PublicationRepository;
use App\Repository\QuestionAnonymeRepository;
use App\Repository\TemoignageRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Back-office « My Amour&Vie ».
 */
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly PublicationRepository $publicationRepository,
        private readonly ProgrammeRepository $programmeRepository,
        private readonly MembreRepository $membreRepository,
        private readonly PartenaireRepository $partenaireRepository,
        private readonly EvenementRepository $evenementRepository,
        private readonly MessageContactRepository $messageContactRepository,
        private readonly TemoignageRepository $temoignageRepository,
        private readonly QuestionAnonymeRepository $questionAnonymeRepository,
        private readonly NewsletterAbonneRepository $newsletterAbonneRepository,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public function index(): Response
    {
        return $this->render('admin/tableau_de_bord.html.twig', [
            'indicateurs' => [
                'articles' => $this->articleRepository->count([]),
                'articlesPublies' => $this->articleRepository->compterParStatut(StatutArticle::PUBLIE),
                'articlesEnAttente' => $this->articleRepository->compterParStatut(StatutArticle::EN_ATTENTE),
                'articlesBrouillons' => $this->articleRepository->compterParStatut(StatutArticle::BROUILLON),
                'publications' => $this->publicationRepository->count([]),
                'programmes' => $this->programmeRepository->compterActifs(),
                'membres' => $this->membreRepository->compterActifs(),
                'partenaires' => $this->partenaireRepository->compterActifs(),
                'evenements' => $this->evenementRepository->compterAVenir(),
                'messagesNonLus' => $this->messageContactRepository->compterNonLus(),
                'temoignagesEnAttente' => $this->temoignageRepository->compterEnAttente(),
                'questionsEnAttente' => $this->questionAnonymeRepository->compterEnAttente(),
                'abonnesNewsletter' => $this->newsletterAbonneRepository->compterActifs(),
            ],
            'derniersArticles' => $this->articleRepository->findBy([], ['updatedAt' => 'DESC', 'id' => 'DESC'], 6),
            'derniersMessages' => $this->messageContactRepository->findBy([], ['createdAt' => 'DESC'], 5),
            'prochainsEvenements' => $this->evenementRepository->trouverAVenir(4),
            'urlArticles' => $this->adminUrlGenerator->setController(ArticleCrudController::class)->setAction('index')->generateUrl(),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<span style="font-weight:800">My</span> Amour&Vie')
            ->setFaviconPath('favicon.ico')
            ->renderContentMaximized()
            ->setLocales(['fr'])
            ->generateRelativeUrls();
    }

    /**
     * Habille EasyAdmin avec l'identité visuelle du site public : sans ce
     * fichier, le back-office garde le thème Bootstrap générique livré par
     * défaut, très différent de la charte Amour&Vie.
     */
    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addAssetMapperEntry('admin-theme');
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        $nom = $user instanceof Utilisateur ? $user->getNomComplet() : $user->getUserIdentifier();

        return parent::configureUserMenu($user)
            ->setName($nom)
            ->displayUserName(true)
            ->addMenuItems([
                MenuItem::linkToUrl('Voir le site public', 'fa fa-globe', $this->generateUrl('accueil'))
                    ->setLinkTarget('_blank'),
                MenuItem::section(),
                MenuItem::linkToLogout('Se déconnecter', 'fa fa-sign-out'),
            ]);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-gauge-high');

        yield MenuItem::section('Contenus éditoriaux');
        yield MenuItem::linkTo(ArticleCrudController::class, 'Articles', 'fa fa-newspaper');
        yield MenuItem::linkTo(PublicationCrudController::class, 'Publications', 'fa fa-file-pdf');
        yield MenuItem::linkTo(EvenementCrudController::class, 'Événements', 'fa fa-calendar-days');
        yield MenuItem::linkTo(EmissionCrudController::class, 'Émissions', 'fa fa-podcast');
        yield MenuItem::linkTo(TemoignageCrudController::class, 'Témoignages', 'fa fa-comment-dots')
            ->setBadge($this->temoignageRepository->compterEnAttente() ?: null, 'danger')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(QuestionAnonymeCrudController::class, 'Questions anonymes', 'fa fa-circle-question')
            ->setBadge($this->questionAnonymeRepository->compterEnAttente() ?: null, 'danger')
            ->setPermission('ROLE_GESTIONNAIRE');

        yield MenuItem::section('Nos actions');
        yield MenuItem::linkTo(ProgrammeCrudController::class, 'Programmes', 'fa fa-diagram-project')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(CentreJeuneCrudController::class, 'Centres Jeunes', 'fa fa-house-medical')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(QuizCrudController::class, 'Quiz', 'fa fa-circle-question')
            ->setPermission('ROLE_GESTIONNAIRE');

        yield MenuItem::section('E-learning')->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkToRoute('Statistiques', 'fa fa-chart-line', 'admin_elearning_statistiques')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(CoursCrudController::class, 'Cours', 'fa fa-graduation-cap')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(ModuleCoursCrudController::class, 'Modules', 'fa fa-layer-group')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(LeconCrudController::class, 'Leçons', 'fa fa-book-open')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(ApprenantCrudController::class, 'Apprenants', 'fa fa-user-graduate')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(InscriptionCoursCrudController::class, 'Suivi des apprenants', 'fa fa-list-check')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(CertificatCrudController::class, 'Certificats', 'fa fa-award')
            ->setPermission('ROLE_GESTIONNAIRE');

        yield MenuItem::section('Institution');
        yield MenuItem::linkTo(MembreCrudController::class, 'Équipe', 'fa fa-users')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(PartenaireCrudController::class, 'Partenaires', 'fa fa-handshake')
            ->setPermission('ROLE_GESTIONNAIRE');

        yield MenuItem::section('Pages du site');
        yield MenuItem::linkTo(SectionPageCrudController::class, 'Sections de page', 'fa fa-file-lines')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(ValeurCrudController::class, 'Nos valeurs', 'fa fa-star')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(ChiffreCleCrudController::class, 'Chiffres clés', 'fa fa-chart-simple')
            ->setPermission('ROLE_GESTIONNAIRE');

        yield MenuItem::section('Médiathèque');
        yield MenuItem::linkTo(MediaCrudController::class, 'Médias', 'fa fa-photo-film');
        yield MenuItem::linkTo(GalerieCrudController::class, 'Galeries', 'fa fa-images');

        yield MenuItem::section('Classification');
        yield MenuItem::linkTo(ThematiqueCrudController::class, 'Thématiques', 'fa fa-tags')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(TypeArticleCrudController::class, "Types d'article", 'fa fa-list')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(TypePublicationCrudController::class, 'Types de publication', 'fa fa-list-check')
            ->setPermission('ROLE_GESTIONNAIRE');

        yield MenuItem::section('Administration');
        yield MenuItem::linkTo(MessageContactCrudController::class, 'Messages reçus', 'fa fa-envelope')
            ->setBadge($this->messageContactRepository->compterNonLus() ?: null, 'danger')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(NewsletterAbonneCrudController::class, 'Abonnés newsletter', 'fa fa-at')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(ParametreCrudController::class, 'Paramètres généraux', 'fa fa-gear')
            ->setPermission('ROLE_SUPER_ADMIN');
        yield MenuItem::linkTo(UtilisateurCrudController::class, 'Utilisateurs', 'fa fa-user-shield')
            ->setPermission('ROLE_SUPER_ADMIN');

        yield MenuItem::section();
        yield MenuItem::linkToUrl('Voir le site', 'fa fa-arrow-up-right-from-square', $this->generateUrl('accueil'))
            ->setLinkTarget('_blank');
    }
}
