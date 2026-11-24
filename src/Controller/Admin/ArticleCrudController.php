<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\Utilisateur;
use App\Enum\StatutArticle;
use App\Security\Voter\ArticleVoter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\Bundle\SecurityBundle\Security;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * Gestion des articles et du workflow éditorial (§ 3.2).
 *
 * Un contributeur ne voit que ses propres articles et ne peut pas les publier :
 * il les soumet à validation, un éditeur ou un super administrateur tranche.
 */
class ArticleCrudController extends AbstractCrudController
{
    public function __construct(private readonly Security $security)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Article')
            ->setEntityLabelInPlural('Articles')
            ->setPageTitle('index', 'Articles et actualités')
            ->setPageTitle('new', 'Rédiger un article')
            ->setPageTitle('edit', fn (Article $article) => sprintf('Modifier « %s »', $article->getTitre()))
            ->setDefaultSort(['datePublication' => 'DESC'])
            ->setSearchFields(['titre', 'sousTitre', 'resume', 'contenu', 'lieu'])
            ->setPaginatorPageSize(20)
            // Vote article par article : un contributeur ne peut pas ouvrir
            // l'article d'un collègue, même en saisissant son URL directement.
            ->setEntityPermission(ArticleVoter::MODIFIER)
            ->setHelp('index', 'Les articles publiés apparaissent immédiatement sur le site. Les articles programmés seront mis en ligne automatiquement à leur date de publication.');
    }

    public function configureActions(Actions $actions): Actions
    {
        $voirSurLeSite = Action::new('voirSurLeSite', 'Voir sur le site', 'fa fa-eye')
            ->linkToUrl(fn (Article $article) => $this->generateUrl('actualite_detail', ['slug' => $article->getSlug()]))
            ->setHtmlAttributes(['target' => '_blank'])
            ->displayIf(static fn (Article $article) => $article->estVisible());

        return $actions
            ->add(Crud::PAGE_INDEX, $voirSurLeSite)
            ->add(Crud::PAGE_EDIT, $voirSurLeSite)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->setPermission(Action::DELETE, 'ROLE_EDITEUR');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('statut', 'Statut')->setChoices(StatutArticle::choices()))
            ->add(EntityFilter::new('programme', 'Programme'))
            ->add(EntityFilter::new('type', 'Type'))
            ->add(EntityFilter::new('thematiques', 'Thématique'))
            ->add(EntityFilter::new('auteur', 'Auteur'))
            ->add(DateTimeFilter::new('datePublication', 'Date de publication'));
    }

    public function configureFields(string $pageName): iterable
    {
        $peutPublier = $this->security->isGranted('ROLE_EDITEUR');

        yield FormField::addTab('Contenu')->setIcon('fa fa-pen');

        yield TextField::new('titre', 'Titre')
            ->setColumns(12)
            ->setHelp('Titre affiché sur le site et dans les résultats de recherche.');

        yield TextField::new('sousTitre', 'Sous-titre')
            ->setColumns(12)
            ->hideOnIndex();

        yield TextareaField::new('resume', 'Résumé')
            ->setColumns(12)
            ->setNumOfRows(3)
            ->hideOnIndex()
            ->setHelp('160 à 300 caractères. Apparaît dans les listes d\'articles et lors des partages sur les réseaux sociaux.');

        yield TextEditorField::new('contenu', 'Contenu de l\'article')
            ->setColumns(12)
            ->hideOnIndex()
            ->setNumOfRows(20);

        yield FormField::addTab('Média')->setIcon('fa fa-image');

        yield ImageField::new('imageName', 'Image')
            ->setBasePath('uploads/articles')
            ->onlyOnIndex();

        yield TextField::new('imageFile', 'Image principale')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions([
                'allow_delete' => true,
                'download_uri' => false,
                'image_uri' => true,
            ])
            ->setColumns(8)
            ->onlyOnForms()
            ->setHelp('Format recommandé : 1600 × 1000 px, en JPG ou WebP.');

        yield TextField::new('imageAlt', 'Texte alternatif de l\'image')
            ->setColumns(8)
            ->onlyOnForms()
            ->setHelp('Décrit l\'image pour les personnes utilisant un lecteur d\'écran. Obligatoire pour l\'accessibilité.');

        yield AssociationField::new('galerie', 'Galerie photos')
            ->setColumns(8)
            ->onlyOnForms()
            ->autocomplete()
            ->setHelp('Images complémentaires affichées en bas de l\'article.');

        yield FormField::addTab('Classement')->setIcon('fa fa-tags');

        yield AssociationField::new('type', 'Type de contenu')
            ->setColumns(6)
            ->setHelp('Actualité, communiqué, analyse, interview…');

        yield AssociationField::new('programme', 'Programme concerné')
            ->setColumns(6);

        yield AssociationField::new('thematiques', 'Thématiques')
            ->setColumns(12)
            ->autocomplete()
            ->hideOnIndex();

        yield TextField::new('lieu', 'Lieu')
            ->setColumns(6)
            ->hideOnIndex();

        yield FormField::addTab('Publication')->setIcon('fa fa-paper-plane');

        yield ChoiceField::new('statut', 'Statut')
            ->setChoices(StatutArticle::choices())
            ->renderAsBadges([
                StatutArticle::BROUILLON->value => 'secondary',
                StatutArticle::EN_ATTENTE->value => 'warning',
                StatutArticle::PLANIFIE->value => 'info',
                StatutArticle::PUBLIE->value => 'success',
                StatutArticle::ARCHIVE->value => 'dark',
            ])
            ->setColumns(6)
            ->setHelp($peutPublier
                ? 'Choisissez « Publié » pour une mise en ligne immédiate, ou « Programmé » avec une date future.'
                : 'Sélectionnez « En attente de validation » pour soumettre votre article à un éditeur.');

        yield DateTimeField::new('datePublication', 'Date de publication')
            ->setColumns(6)
            ->setHelp('Pour un article programmé, la mise en ligne se fera automatiquement à cette date.');

        yield BooleanField::new('aLaUne', 'À la une')
            ->setColumns(6)
            ->setHelp('Met l\'article en avant sur la page d\'accueil.');

        yield AssociationField::new('auteur', 'Auteur (compte)')
            ->setColumns(6)
            ->hideOnIndex()
            ->setPermission('ROLE_EDITEUR');

        yield TextField::new('auteurLibre', 'Signature libre')
            ->setColumns(6)
            ->hideOnIndex()
            ->setHelp('À renseigner si l\'auteur n\'a pas de compte sur My Amour&Vie.');

        yield FormField::addTab('Référencement')->setIcon('fa fa-magnifying-glass');

        yield TextField::new('seoTitre', 'Titre SEO')
            ->setColumns(8)
            ->onlyOnForms()
            ->setHelp('Laissez vide pour utiliser le titre de l\'article. 60 caractères maximum recommandés.');

        yield TextareaField::new('seoDescription', 'Méta-description')
            ->setColumns(8)
            ->setNumOfRows(2)
            ->onlyOnForms()
            ->setHelp('Laissez vide pour utiliser le résumé. 160 caractères maximum recommandés.');

        // Colonnes visibles uniquement dans la liste.
        yield IntegerField::new('nombreVues', 'Vues')->onlyOnIndex();
        yield DateTimeField::new('updatedAt', 'Modifié le')->onlyOnDetail();
    }

    /**
     * Un contributeur ne voit que ses propres articles (§ 3.13).
     */
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        if (!$this->security->isGranted('ROLE_EDITEUR')) {
            $qb->andWhere('entity.auteur = :utilisateurCourant')
                ->setParameter('utilisateurCourant', $this->getUser());
        }

        return $qb;
    }

    public function createEntity(string $entityFqcn): Article
    {
        $article = new Article();

        $utilisateur = $this->getUser();
        if ($utilisateur instanceof Utilisateur) {
            $article->setAuteur($utilisateur);
        }

        return $article;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->appliquerRegleDePublication($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->appliquerRegleDePublication($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    /**
     * Garde-fou du workflow : un contributeur ne peut pas publier lui-même.
     * Toute tentative est ramenée à « En attente de validation ».
     */
    private function appliquerRegleDePublication(mixed $entity): void
    {
        if (!$entity instanceof Article) {
            return;
        }

        if ($this->security->isGranted('ROLE_EDITEUR')) {
            return;
        }

        if (\in_array($entity->getStatut(), [StatutArticle::PUBLIE, StatutArticle::PLANIFIE], true)) {
            $entity->setStatut(StatutArticle::EN_ATTENTE);

            $this->addFlash(
                'warning',
                'Votre article a été enregistré en attente de validation : seul un éditeur peut le publier.'
            );
        }
    }
}
