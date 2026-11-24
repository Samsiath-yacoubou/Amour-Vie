<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Membre;
use App\Enum\CategorieMembre;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * Gestion de l'équipe : conseil d'administration, coordination,
 * équipe technique (§ 3.5).
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class MembreCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Membre::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Membre')
            ->setEntityLabelInPlural('Équipe')
            ->setPageTitle('index', 'Membres de l\'organisation')
            ->setDefaultSort(['categorie' => 'ASC', 'ordre' => 'ASC'])
            ->setSearchFields(['prenom', 'nom', 'fonction', 'departement'])
            ->setPaginatorPageSize(30)
            ->setHelp('index', 'Les membres apparaissent sur la page « Qui sommes-nous ? », regroupés par catégorie et triés par ordre d\'affichage.');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('categorie', 'Catégorie')->setChoices(CategorieMembre::choices()));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Identité')->setIcon('fa fa-user');

        yield ImageField::new('photoName', 'Photo')
            ->setBasePath('uploads/equipe')
            ->onlyOnIndex();

        yield TextField::new('prenom', 'Prénom')->setColumns(6);
        yield TextField::new('nom', 'Nom')->setColumns(6);

        yield TextField::new('photoFile', 'Photo du membre')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false, 'image_uri' => true])
            ->setColumns(6)
            ->onlyOnForms()
            ->setHelp('Portrait au format carré, 600 × 600 px minimum.');

        yield FormField::addFieldset('Fonction')->setIcon('fa fa-briefcase');

        yield TextField::new('fonction', 'Fonction')->setColumns(6);

        yield TextField::new('departement', 'Département')
            ->setColumns(6)
            ->setHelp('Ex. : Programmes, Communication, Administration et finances.');

        yield ChoiceField::new('categorie', 'Catégorie')
            ->setChoices(CategorieMembre::choices())
            ->renderAsBadges([
                CategorieMembre::CONSEIL_ADMINISTRATION->value => 'primary',
                CategorieMembre::COORDINATION->value => 'info',
                CategorieMembre::EQUIPE_TECHNIQUE->value => 'secondary',
            ])
            ->setColumns(6);

        yield TextareaField::new('biographie', 'Biographie')
            ->setColumns(12)
            ->setNumOfRows(5)
            ->hideOnIndex();

        yield FormField::addFieldset('Contact et affichage')->setIcon('fa fa-address-card');

        yield EmailField::new('email', 'E-mail professionnel')
            ->setColumns(6)
            ->hideOnIndex();

        yield UrlField::new('linkedin', 'Profil LinkedIn')
            ->setColumns(6)
            ->hideOnIndex();

        yield IntegerField::new('ordre', 'Ordre d\'affichage')->setColumns(6);
        yield BooleanField::new('actif', 'Actif')->setColumns(6);
    }
}
