<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\SectionPage;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * Blocs de contenu des pages institutionnelles (§ 3.9 et § 3.10).
 *
 * C'est ce mécanisme qui permet d'ajouter de nouvelles sections — voire de
 * nouvelles pages — sans intervention technique (§ 5.1).
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class SectionPageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SectionPage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Section de page')
            ->setEntityLabelInPlural('Sections de page')
            ->setPageTitle('index', 'Contenus des pages')
            ->setDefaultSort(['page' => 'ASC', 'ordre' => 'ASC'])
            ->setSearchFields(['titre', 'sousTitre', 'contenu', 'cle'])
            ->setHelp(
                'index',
                'Chaque section correspond à un bloc de contenu d\'une page. La « clé » est l\'identifiant technique '
                .'utilisé par le gabarit : histoire, mission, vision, presentation, methodologie…'
            );
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('page', 'Page')->setChoices(SectionPage::PAGES));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Emplacement')->setIcon('fa fa-location-dot');

        yield ChoiceField::new('page', 'Page de rattachement')
            ->setChoices(SectionPage::PAGES)
            ->renderAsBadges()
            ->setColumns(6);

        yield TextField::new('cle', 'Clé technique')
            ->setColumns(6)
            ->setHelp('Minuscules, chiffres et tirets uniquement. Exemples : histoire, mission, vision, presentation.');

        yield IntegerField::new('ordre', 'Ordre')->setColumns(6);
        yield BooleanField::new('actif', 'Affichée')->setColumns(6);

        yield FormField::addFieldset('Contenu')->setIcon('fa fa-align-left');

        yield TextField::new('titre', 'Titre de la section')->setColumns(12);

        yield TextField::new('sousTitre', 'Sur-titre / sous-titre')
            ->setColumns(12)
            ->hideOnIndex();

        yield TextEditorField::new('contenu', 'Contenu')
            ->setColumns(12)
            ->setNumOfRows(14)
            ->hideOnIndex();

        yield FormField::addFieldset('Illustration')->setIcon('fa fa-image');

        yield ImageField::new('imageName', 'Image')
            ->setBasePath('/uploads/pages')
            ->onlyOnIndex();

        yield TextField::new('imageFile', 'Image')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false, 'image_uri' => true])
            ->setColumns(8)
            ->onlyOnForms();

        yield TextField::new('imageAlt', 'Texte alternatif')
            ->setColumns(8)
            ->onlyOnForms()
            ->setHelp('Description de l\'image pour les lecteurs d\'écran.');

        yield AssociationField::new('galerie', 'Galerie associée')
            ->setColumns(8)
            ->hideOnIndex();
    }
}
