<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Parametre;
use App\Repository\ParametreRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\HttpFoundation\Response;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * Paramètres généraux du site (§ 3.10 et § 3.12).
 *
 * L'entité est un enregistrement unique : la liste redirige directement
 * vers le formulaire d'édition.
 */
#[IsGranted('ROLE_SUPER_ADMIN')]
class ParametreCrudController extends AbstractCrudController
{
    public function __construct(private readonly ParametreRepository $parametreRepository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Parametre::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Paramètres généraux')
            ->setEntityLabelInPlural('Paramètres généraux')
            ->setPageTitle('edit', 'Paramètres généraux du site');
    }

    public function configureActions(Actions $actions): Actions
    {
        // Un seul enregistrement : ni création ni suppression.
        return $actions
            ->disable(Action::NEW, Action::DELETE, Action::BATCH_DELETE);
    }

    /**
     * Court-circuite la liste : on édite toujours l'unique enregistrement.
     *
     * La redirection cible la route nommée plutôt que l'AdminUrlGenerator,
     * qui reconstruirait l'URL de l'index à partir du contexte courant et
     * provoquerait une boucle.
     */
    public function index(AdminContext $context): Response
    {
        return $this->redirectToRoute('admin_parametre_edit', [
            'entityId' => $this->parametreRepository->obtenir()->getId(),
        ]);
    }

    public function configureFields(string $pageName): iterable
    {
        // ------------------------------------------------------------ Identité
        yield FormField::addTab('Identité du site')->setIcon('fa fa-id-badge');

        yield TextField::new('nomSite', 'Nom du site')->setColumns(6);
        yield TextField::new('slogan', 'Slogan')->setColumns(6);

        yield TextField::new('logoFile', 'Logo principal')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false, 'image_uri' => true])
            ->setColumns(4)->onlyOnForms();

        yield TextField::new('logoBlancFile', 'Logo pour fond sombre')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false, 'image_uri' => true])
            ->setColumns(4)->onlyOnForms()
            ->setHelp('Version claire du logo, utilisée dans le pied de page.');

        yield TextField::new('faviconFile', 'Favicon')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false, 'image_uri' => true])
            ->setColumns(4)->onlyOnForms()
            ->setHelp('Icône affichée dans l\'onglet du navigateur. 32 × 32 px ou 64 × 64 px.');

        // ---------------------------------------------------- Page d'accueil
        yield FormField::addTab('Page d\'accueil')->setIcon('fa fa-house');

        yield TextField::new('heroTitre', 'Titre principal')
            ->setColumns(12)
            ->setHelp('Message fort affiché sur le bandeau d\'accueil.');

        yield TextareaField::new('heroSousTitre', 'Texte d\'introduction')
            ->setColumns(12)->setNumOfRows(3);

        yield TextField::new('heroImageFile', 'Image du bandeau')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false, 'image_uri' => true])
            ->setColumns(6)->onlyOnForms()
            ->setHelp('Format paysage, 1920 × 1080 px minimum.');

        yield UrlField::new('heroVideoUrl', 'Vidéo institutionnelle (URL)')
            ->setColumns(6)
            ->setHelp('Si renseignée, la vidéo remplace l\'image du bandeau.');

        yield TextField::new('heroBouton1Libelle', 'Bouton 1 : libellé')->setColumns(3);
        yield TextField::new('heroBouton1Lien', 'Bouton 1 : lien')->setColumns(3);
        yield TextField::new('heroBouton2Libelle', 'Bouton 2 : libellé')->setColumns(3);
        yield TextField::new('heroBouton2Lien', 'Bouton 2 : lien')->setColumns(3);

        // ------------------------------------------------------- Coordonnées
        yield FormField::addTab('Coordonnées')->setIcon('fa fa-address-book');

        yield TextareaField::new('adresse', 'Adresse postale')
            ->setColumns(6)->setNumOfRows(3);

        yield TelephoneField::new('telephone', 'Téléphone principal')->setColumns(3);
        yield TelephoneField::new('telephoneSecondaire', 'Téléphone secondaire')->setColumns(3);
        yield EmailField::new('email', 'Adresse e-mail')
            ->setColumns(6)
            ->setHelp('Reçoit également les messages du formulaire de contact.');

        yield TextField::new('horaires', 'Horaires d\'ouverture')
            ->setColumns(6)
            ->setHelp('Ex. : Du lundi au vendredi, de 8h à 17h.');

        yield TextareaField::new('mapsEmbed', 'Plan d\'accès (code d\'intégration)')
            ->setColumns(12)->setNumOfRows(4)
            ->setHelp('Collez ici le code &lt;iframe&gt; fourni par Google Maps.');

        // ---------------------------------------------------- Réseaux sociaux
        yield FormField::addTab('Réseaux sociaux')->setIcon('fa fa-share-nodes');

        yield UrlField::new('facebook', 'Facebook')->setColumns(6);
        yield UrlField::new('instagram', 'Instagram')->setColumns(6);
        yield UrlField::new('tiktok', 'TikTok')->setColumns(6);
        yield UrlField::new('youtube', 'YouTube')->setColumns(6);
        yield UrlField::new('linkedin', 'LinkedIn')->setColumns(6);
        yield UrlField::new('twitter', 'X (Twitter)')->setColumns(6);

        yield BooleanField::new('afficherFilReseauxSociaux', 'Afficher le fil sur la page d\'accueil')
            ->setColumns(12)
            ->setHelp('Montre les dernières publications de tout le réseau Amour&Vie sur l\'accueil.');

        yield TextField::new('filReseauxSociauxTitre', 'Titre de la section')->setColumns(12);

        yield TextareaField::new('filReseauxSociauxCode', 'Fil des réseaux sociaux (code d\'intégration)')
            ->setColumns(12)->setNumOfRows(6)
            ->setHelp(
                'Collez ici le code fourni par un agrégateur de réseaux sociaux '
                .'(Elfsight, Juicer, SociableKIT…) réunissant les pages Facebook, '
                .'Instagram, YouTube… de tout le réseau du programme.'
            );

        // ------------------------------------------------------------ Footer
        yield FormField::addTab('Pied de page')->setIcon('fa fa-shoe-prints');

        yield TextareaField::new('footerTexte', 'Texte de présentation')
            ->setColumns(12)->setNumOfRows(4);

        yield TextField::new('copyright', 'Mention de copyright')->setColumns(12);

        // --------------------------------------------------------------- SEO
        yield FormField::addTab('Référencement')->setIcon('fa fa-magnifying-glass');

        yield TextField::new('seoTitre', 'Titre SEO par défaut')->setColumns(12);

        yield TextareaField::new('seoDescription', 'Méta-description par défaut')
            ->setColumns(12)->setNumOfRows(3)
            ->setHelp('160 caractères maximum recommandés.');

        yield TextField::new('seoMotsCles', 'Mots-clés')
            ->setColumns(12)
            ->setHelp('Séparés par des virgules.');

        yield TextField::new('imagePartageFile', 'Image de partage social')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false, 'image_uri' => true])
            ->setColumns(6)->onlyOnForms()
            ->setHelp('Affichée lors du partage d\'un lien du site. Format 1200 × 630 px.');

        // ----------------------------------------------------- Certificats e-learning
        yield FormField::addTab('Certificats')->setIcon('fa fa-award');

        yield TextField::new('certificatSignataireNom', 'Nom du signataire')
            ->setColumns(6)->hideOnIndex()
            ->setHelp('Personne qui signe les certificats au nom de l\'ABMS (accord de l\'ABMS requis).');
        yield TextField::new('certificatSignataireFonction', 'Fonction du signataire')
            ->setColumns(6)->hideOnIndex()
            ->setHelp('Ex. : Directeur·rice exécutif·ve, ABMS.');
        yield TextField::new('certificatSignatureFile', 'Signature')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false, 'image_uri' => true])
            ->setColumns(6)->onlyOnForms()
            ->setHelp('Image PNG à fond transparent, de préférence. Utilisée sur tous les nouveaux certificats et lors des réémissions.');

        // ----------------------------------------------------- Pages légales
        yield FormField::addTab('Pages légales')->setIcon('fa fa-scale-balanced');

        yield TextEditorField::new('mentionsLegales', 'Mentions légales')
            ->setColumns(12)->setNumOfRows(12);

        yield TextEditorField::new('politiqueConfidentialite', 'Politique de confidentialité')
            ->setColumns(12)->setNumOfRows(12);

        yield TextEditorField::new('conditionsUtilisation', 'Conditions d\'utilisation')
            ->setColumns(12)->setNumOfRows(12);
    }
}
