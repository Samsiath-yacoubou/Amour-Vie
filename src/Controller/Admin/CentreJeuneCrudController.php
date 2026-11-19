<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\CentreJeune;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * Gestion des Centres Jeunes Amour&Vie (annuaire + carte publique).
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class CentreJeuneCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CentreJeune::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Centre Jeune')
            ->setEntityLabelInPlural('Centres Jeunes')
            ->setPageTitle('index', 'Centres Jeunes Amour&Vie')
            ->setDefaultSort(['ordre' => 'ASC', 'nom' => 'ASC'])
            ->setSearchFields(['nom', 'ville', 'adresse', 'description'])
            ->setPaginatorPageSize(25)
            ->setHelp('index', 'Renseignez la latitude et la longitude pour faire apparaître le centre sur la carte publique.');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('ville', 'Ville'))
            ->add(EntityFilter::new('programme', 'Programme'))
            ->add(EntityFilter::new('thematiques', 'Services'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Informations')->setIcon('fa fa-house-medical');

        yield TextField::new('nom', 'Nom du centre')->setColumns(12);

        yield TextField::new('ville', 'Ville')->setColumns(6);

        yield TextareaField::new('adresse', 'Adresse')
            ->setColumns(6)
            ->setNumOfRows(3);

        yield TextareaField::new('description', 'Présentation')
            ->setColumns(12)
            ->setNumOfRows(4)
            ->hideOnIndex();

        yield TelephoneField::new('telephone', 'Téléphone')->setColumns(4);
        yield EmailField::new('email', 'E-mail')->setColumns(4)->hideOnIndex();
        yield TextField::new('horaires', 'Horaires')
            ->setColumns(4)
            ->setHelp('Ex. : Du mardi au samedi, de 9h à 18h.');

        yield TextField::new('responsable', 'Responsable')->setColumns(6)->hideOnIndex();

        yield AssociationField::new('programme', 'Programme')->setColumns(6);
        yield AssociationField::new('thematiques', 'Services offerts')
            ->setColumns(12)
            ->autocomplete()
            ->hideOnIndex();

        yield TextField::new('imageFile', 'Photo')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false, 'image_uri' => true])
            ->setColumns(6)
            ->onlyOnForms();

        yield FormField::addFieldset('Localisation')->setIcon('fa fa-map-location-dot');

        yield NumberField::new('latitude', 'Latitude')
            ->setNumDecimals(7)
            ->setColumns(6)
            ->hideOnIndex()
            ->setHelp('Exemple pour Cotonou : 6.3703');

        yield NumberField::new('longitude', 'Longitude')
            ->setNumDecimals(7)
            ->setColumns(6)
            ->hideOnIndex()
            ->setHelp('Exemple pour Cotonou : 2.3912');

        yield IntegerField::new('ordre', "Ordre d'affichage")->setColumns(6)->hideOnIndex();
        yield BooleanField::new('actif', 'Actif')->setColumns(6);
    }
}
