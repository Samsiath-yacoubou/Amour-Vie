<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Publication;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * Gestion de la bibliothèque numérique (§ 3.3).
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class PublicationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Publication::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Publication')
            ->setEntityLabelInPlural('Publications')
            ->setPageTitle('index', 'Bibliothèque numérique')
            ->setPageTitle('new', 'Ajouter un document')
            ->setDefaultSort(['annee' => 'DESC', 'createdAt' => 'DESC'])
            ->setSearchFields(['titre', 'description'])
            ->setPaginatorPageSize(20)
            ->setHelp('index', 'Les documents publiés sont téléchargeables librement depuis la rubrique « Publications » du site.');
    }

    public function configureActions(Actions $actions): Actions
    {
        $voirSurLeSite = Action::new('voirSurLeSite', 'Voir sur le site', 'fa fa-eye')
            ->linkToUrl(fn (Publication $publication) => $this->generateUrl('publication_detail', ['slug' => $publication->getSlug()]))
            ->setHtmlAttributes(['target' => '_blank'])
            ->displayIf(static fn (Publication $publication) => $publication->isPublie());

        return $actions
            ->add(Crud::PAGE_INDEX, $voirSurLeSite)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->setPermission(Action::DELETE, 'ROLE_EDITEUR');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('type', 'Type de document'))
            ->add(EntityFilter::new('programme', 'Programme'))
            ->add(EntityFilter::new('thematiques', 'Thématique'))
            ->add(NumericFilter::new('annee', 'Année'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addTab('Informations')->setIcon('fa fa-circle-info');

        yield TextField::new('titre', 'Titre du document')->setColumns(12);

        yield TextEditorField::new('description', 'Description')
            ->setColumns(12)
            ->hideOnIndex()
            ->setNumOfRows(8);

        yield AssociationField::new('type', 'Type de document')
            ->setColumns(6)
            ->setHelp('Rapport, étude, guide, note de politique…');

        yield IntegerField::new('annee', 'Année de publication')->setColumns(6);

        yield AssociationField::new('programme', 'Programme concerné')->setColumns(6);

        yield AssociationField::new('thematiques', 'Thématiques')
            ->setColumns(12)
            ->autocomplete()
            ->hideOnIndex();

        yield FormField::addTab('Fichiers')->setIcon('fa fa-file-arrow-up');

        yield TextField::new('fichierFile', 'Document à télécharger')
            ->setFormType(VichFileType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => true])
            ->setColumns(8)
            ->onlyOnForms()
            ->setHelp('Formats acceptés : PDF ou Word. Taille maximale : 64 Mo.');

        yield ImageField::new('couvertureName', 'Couverture')
            ->setBasePath('/uploads/publications/couvertures')
            ->onlyOnIndex();

        yield TextField::new('couvertureFile', 'Image de couverture')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false, 'image_uri' => true])
            ->setColumns(8)
            ->onlyOnForms()
            ->setHelp('Idéalement la première page du document, au format portrait.');

        yield FormField::addTab('Diffusion')->setIcon('fa fa-share-nodes');

        yield BooleanField::new('publie', 'Publié')
            ->setColumns(6)
            ->setHelp('Décochez pour retirer temporairement le document du site.');

        yield BooleanField::new('aLaUne', 'Mettre en avant')
            ->setColumns(6)
            ->hideOnIndex();

        yield IntegerField::new('nombreTelechargements', 'Téléchargements')
            ->onlyOnIndex();
    }
}
