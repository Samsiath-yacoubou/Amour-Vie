<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Enum\TypeMedia;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Vich\UploaderBundle\Form\Type\VichFileType;

/**
 * Médiathèque : photos, vidéos, audios et documents (§ 3.11).
 */
class MediaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Media::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Média')
            ->setEntityLabelInPlural('Médiathèque')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['nom', 'alt', 'legende'])
            ->setPaginatorPageSize(30)
            ->setHelp('index', 'Les médias déposés ici sont réutilisables dans les articles, les programmes et les pages du site.');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('type', 'Type')->setChoices(TypeMedia::choices()))
            ->add(EntityFilter::new('galerie', 'Galerie'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Fichier')->setIcon('fa fa-photo-film');

        yield ImageField::new('fichierName', 'Aperçu')
            ->setBasePath('/uploads/mediatheque')
            ->onlyOnIndex();

        yield TextField::new('nom', 'Nom du média')->setColumns(8);

        yield ChoiceField::new('type', 'Type')
            ->setChoices(TypeMedia::choices())
            ->renderAsBadges()
            ->setColumns(4);

        yield TextField::new('fichierFile', 'Fichier')
            ->setFormType(VichFileType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => true])
            ->setColumns(8)
            ->onlyOnForms()
            ->setHelp('Taille maximale : 64 Mo.');

        yield UrlField::new('urlExterne', 'URL externe')
            ->setColumns(8)
            ->hideOnIndex()
            ->setHelp('Pour les vidéos hébergées sur YouTube ou Vimeo, plutôt que de téléverser le fichier.');

        yield FormField::addFieldset('Accessibilité et classement')->setIcon('fa fa-universal-access');

        yield TextField::new('alt', 'Texte alternatif')
            ->setColumns(8)
            ->hideOnIndex()
            ->setHelp('Décrit l\'image pour les lecteurs d\'écran. Obligatoire pour la conformité RGAA.');

        yield TextareaField::new('legende', 'Légende')
            ->setColumns(8)
            ->setNumOfRows(2)
            ->hideOnIndex();

        yield AssociationField::new('galerie', 'Galerie')->setColumns(6);

        yield IntegerField::new('ordre', 'Ordre')->setColumns(6)->hideOnIndex();

        yield TextField::new('tailleLisible', 'Taille')->onlyOnIndex();
    }
}
