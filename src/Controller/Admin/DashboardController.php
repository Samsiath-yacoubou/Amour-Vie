<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Utilisateur;
use App\Enum\StatutArticle;
use App\Enum\StatutModeration;
use App\Enum\StatutQuestion;
use App\Repository\ApprenantRepository;
use App\Repository\ArticleRepository;
use App\Repository\CentreJeuneRepository;
use App\Repository\CertificatRepository;
use App\Repository\EmissionRepository;
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
#[AdminDashboard(routePath: '/mastering', routeName: 'admin')]
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
        private readonly CentreJeuneRepository $centreJeuneRepository,
        private readonly EmissionRepository $emissionRepository,
        private readonly ApprenantRepository $apprenantRepository,
        private readonly CertificatRepository $certificatRepository,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public function index(): Response
    {
        $peutValider = $this->isGranted('ROLE_EDITEUR');
        // Témoignages, questions, messages et rubriques institutionnelles sont
        // réservés aux gestionnaires (#[IsGranted] sur leurs CrudController) :
        // un contributeur n'y verrait que des liens menant à un 403.
        $gestion = $this->isGranted('ROLE_GESTIONNAIRE');

        // « À traiter » : ce qui attend une action, avec un lien vers la liste
        // déjà filtrée. Les articles à valider ne concernent que les éditeurs.
        $aTraiter = [];
        if ($peutValider) {
            $aTraiter[] = [
                'libelle' => 'Articles à valider', 'icone' => 'fa-solid fa-file-circle-check',
                'nombre' => $this->articleRepository->compterParStatut(StatutArticle::EN_ATTENTE),
                'url' => $this->urlListe(ArticleCrudController::class, ['statut' => StatutArticle::EN_ATTENTE]),
            ];
        }
        if (!$peutValider && $this->getUser() instanceof Utilisateur) {
            // Contributeur : ses propres brouillons à terminer et ce qu'il a soumis.
            $aTraiter[] = [
                'libelle' => 'Mes brouillons à terminer', 'icone' => 'fa-solid fa-file-pen',
                'nombre' => $this->articleRepository->count(['auteur' => $this->getUser(), 'statut' => StatutArticle::BROUILLON]),
                'url' => $this->urlListe(ArticleCrudController::class, ['statut' => StatutArticle::BROUILLON]),
            ];
            $aTraiter[] = [
                'libelle' => 'Soumis, en attente de validation', 'icone' => 'fa-solid fa-hourglass-half',
                'nombre' => $this->articleRepository->count(['auteur' => $this->getUser(), 'statut' => StatutArticle::EN_ATTENTE]),
                'url' => $this->urlListe(ArticleCrudController::class, ['statut' => StatutArticle::EN_ATTENTE]),
                'information' => true,
            ];
        }
        if ($gestion) {
            $aTraiter[] = [
                'libelle' => 'Témoignages à modérer', 'icone' => 'fa-solid fa-comment-dots',
                'nombre' => $this->temoignageRepository->compterEnAttente(),
                'url' => $this->urlListe(TemoignageCrudController::class, ['statut' => StatutModeration::EN_ATTENTE]),
            ];
            $aTraiter[] = [
                'libelle' => 'Questions anonymes', 'icone' => 'fa-solid fa-circle-question',
                'nombre' => $this->questionAnonymeRepository->compterEnAttente(),
                'url' => $this->urlListe(QuestionAnonymeCrudController::class, ['statut' => StatutQuestion::EN_ATTENTE]),
            ];
            $aTraiter[] = [
                'libelle' => 'Messages non lus', 'icone' => 'fa-solid fa-envelope',
                'nombre' => $this->messageContactRepository->compterNonLus(),
                'url' => $this->urlListe(MessageContactCrudController::class, ['lu' => '0']),
            ];
        }

        $publies = $this->articleRepository->compterParStatut(StatutArticle::PUBLIE);
        $lectures = (int) $this->articleRepository->createQueryBuilder('a')
            ->select('COALESCE(SUM(a.nombreVues), 0)')
            ->getQuery()->getSingleScalarResult();

        return $this->render('admin/tableau_de_bord.html.twig', [
            'aTraiter' => $aTraiter,
            // Seules les cartes qui demandent une action comptent dans le total.
            'totalATraiter' => array_sum(array_column(array_filter($aTraiter, static fn ($t) => empty($t['information'])), 'nombre')),
            'cles' => [
                'publies' => $publies,
                'brouillons' => $this->articleRepository->compterParStatut(StatutArticle::BROUILLON),
                'planifies' => $this->articleRepository->compterParStatut(StatutArticle::PLANIFIE),
                'lectures' => $lectures,
                'lecturesParArticle' => $publies > 0 ? (int) round($lectures / $publies) : 0,
                'abonnes' => $this->newsletterAbonneRepository->compterActifs(),
                'apprenants' => $this->apprenantRepository->count([]),
                'certificats' => $this->certificatRepository->compterValides(),
            ],
            'publicationsParMois' => $this->publicationsParMois(6),
            'plusLus' => $this->articleRepository->findBy(['statut' => StatutArticle::PUBLIE], ['nombreVues' => 'DESC'], 5),
            'inventaire' => !$gestion ? [] : [
                ['libelle' => 'Publications', 'nombre' => $this->publicationRepository->count([]), 'icone' => 'fa-solid fa-file-pdf', 'url' => $this->urlListe(PublicationCrudController::class)],
                ['libelle' => 'Émissions', 'nombre' => $this->emissionRepository->count([]), 'icone' => 'fa-solid fa-podcast', 'url' => $this->urlListe(EmissionCrudController::class)],
                ['libelle' => 'Programmes actifs', 'nombre' => $this->programmeRepository->compterActifs(), 'icone' => 'fa-solid fa-diagram-project', 'url' => $this->urlListe(ProgrammeCrudController::class)],
                ['libelle' => 'Centres Jeunes', 'nombre' => $this->centreJeuneRepository->count([]), 'icone' => 'fa-solid fa-house', 'url' => $this->urlListe(CentreJeuneCrudController::class)],
                ['libelle' => 'Membres de l\'équipe', 'nombre' => $this->membreRepository->compterActifs(), 'icone' => 'fa-solid fa-users', 'url' => $this->urlListe(MembreCrudController::class)],
                ['libelle' => 'Partenaires', 'nombre' => $this->partenaireRepository->compterActifs(), 'icone' => 'fa-solid fa-handshake', 'url' => $this->urlListe(PartenaireCrudController::class)],
            ],
            // Contributeur : ses propres articles (ceux qu'il peut ouvrir).
            'derniersArticles' => $this->articleRepository->findBy($peutValider ? [] : ['auteur' => $this->getUser()], ['updatedAt' => 'DESC', 'id' => 'DESC'], 5),
            'gestion' => $gestion,
            'derniersMessages' => $gestion ? $this->messageContactRepository->findBy([], ['createdAt' => 'DESC'], 4) : [],
            'prochainsEvenements' => $this->evenementRepository->trouverAVenir(4),
            'urls' => [
                'nouvelArticle' => $this->adminUrlGenerator->unsetAll()->setController(ArticleCrudController::class)->setAction('new')->generateUrl(),
                'nouvelEvenement' => $this->isGranted('ROLE_GESTIONNAIRE')
                    ? $this->adminUrlGenerator->unsetAll()->setController(EvenementCrudController::class)->setAction('new')->generateUrl()
                    : null,
                'articles' => $this->urlListe(ArticleCrudController::class),
                'evenements' => $this->urlListe(EvenementCrudController::class),
                'messages' => $this->urlListe(MessageContactCrudController::class),
            ],
        ]);
    }

    /**
     * URL d'une liste EasyAdmin, éventuellement filtrée (filtre « égal à »).
     *
     * @param array<string, \UnitEnum|string> $filtres
     */
    private function urlListe(string $controleur, array $filtres = []): string
    {
        $generateur = $this->adminUrlGenerator->unsetAll()->setController($controleur)->setAction('index');
        if ($filtres) {
            $generateur->set('filters', array_map(
                // Les ChoiceFilter reçoivent des cases d'enum : Symfony identifie
                // alors chaque choix par sa POSITION dans la liste, pas par sa
                // valeur (« en_attente » est ignoré, « 1 » fonctionne).
                static fn ($v) => ['comparison' => '=', 'value' => $v instanceof \UnitEnum ? array_search($v, $v::cases(), true) : $v],
                $filtres,
            ));
        }

        return $generateur->generateUrl();
    }

    /**
     * Articles publiés par mois sur les $nombre derniers mois (mois courant
     * inclus), y compris les mois sans publication : [['mois' => 'nov.', 'annee' => 2026, 'nombre' => 3], …].
     */
    private function publicationsParMois(int $nombre): array
    {
        $debut = (new \DateTimeImmutable('first day of this month midnight'))->modify(sprintf('-%d months', $nombre - 1));
        $dates = $this->articleRepository->createQueryBuilder('a')
            ->select('a.datePublication')
            ->andWhere('a.statut = :statut')->setParameter('statut', StatutArticle::PUBLIE)
            ->andWhere('a.datePublication >= :debut')->setParameter('debut', $debut)
            ->getQuery()->getSingleColumnResult();

        $abreges = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
        $complets = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
        $mois = [];
        for ($k = 0; $k < $nombre; ++$k) {
            $m = $debut->modify("+$k months");
            $mois[$m->format('Y-m')] = ['mois' => $abreges[(int) $m->format('n') - 1], 'moisComplet' => $complets[(int) $m->format('n') - 1], 'annee' => (int) $m->format('Y'), 'nombre' => 0];
        }
        foreach ($dates as $date) {
            $cle = ($date instanceof \DateTimeInterface ? $date : new \DateTimeImmutable((string) $date))->format('Y-m');
            if (isset($mois[$cle])) {
                ++$mois[$cle]['nombre'];
            }
        }

        return array_values($mois);
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

        // Les entrées réservées aux gestionnaires portent la même permission que
        // le #[IsGranted] de leur CrudController : un contributeur ne voit que ce
        // qu'il peut ouvrir (sinon : lien → 403).
        yield MenuItem::section('Contenus éditoriaux');
        yield MenuItem::linkTo(ArticleCrudController::class, 'Articles', 'fa fa-newspaper');
        yield MenuItem::linkTo(PublicationCrudController::class, 'Publications', 'fa fa-file-pdf')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(EvenementCrudController::class, 'Événements', 'fa fa-calendar-days')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(EmissionCrudController::class, 'Émissions', 'fa fa-podcast')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(TemoignageCrudController::class, 'Témoignages', 'fa fa-comment-dots')
            ->setBadge($this->temoignageRepository->compterEnAttente() ?: null, 'danger')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(QuestionAnonymeCrudController::class, 'Questions anonymes', 'fa fa-circle-question')
            ->setBadge($this->questionAnonymeRepository->compterEnAttente() ?: null, 'danger')
            ->setPermission('ROLE_GESTIONNAIRE');

        yield MenuItem::section('Nos actions')->setPermission('ROLE_GESTIONNAIRE');
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

        yield MenuItem::section('Institution')->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(MembreCrudController::class, 'Équipe', 'fa fa-users')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(PartenaireCrudController::class, 'Partenaires', 'fa fa-handshake')
            ->setPermission('ROLE_GESTIONNAIRE');

        yield MenuItem::section('Pages du site')->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(SectionPageCrudController::class, 'Sections de page', 'fa fa-file-lines')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(ValeurCrudController::class, 'Nos valeurs', 'fa fa-star')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(ChiffreCleCrudController::class, 'Chiffres clés', 'fa fa-chart-simple')
            ->setPermission('ROLE_GESTIONNAIRE');

        yield MenuItem::section('Médiathèque');
        yield MenuItem::linkTo(MediaCrudController::class, 'Médias', 'fa fa-photo-film');
        yield MenuItem::linkTo(GalerieCrudController::class, 'Galeries', 'fa fa-images');

        yield MenuItem::section('Classification')->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(ThematiqueCrudController::class, 'Thématiques', 'fa fa-tags')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(TypeArticleCrudController::class, "Types d'article", 'fa fa-list')
            ->setPermission('ROLE_GESTIONNAIRE');
        yield MenuItem::linkTo(TypePublicationCrudController::class, 'Types de publication', 'fa fa-list-check')
            ->setPermission('ROLE_GESTIONNAIRE');

        yield MenuItem::section('Administration')->setPermission('ROLE_GESTIONNAIRE');
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
