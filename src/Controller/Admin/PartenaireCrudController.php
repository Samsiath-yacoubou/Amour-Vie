<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Partenaire;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * Gestion des partenaires (§ 3.6).
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class PartenaireCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Partenaire::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Partenaire')
            ->setEntityLabelInPlural('Partenaires')
            ->setDefaultSort(['ordre' => 'ASC'])
            ->setSearchFields(['nom', 'categorie', 'description'])
            ->setHelp('index', 'Les partenaires actifs apparaissent dans la bande logos de la page d\'accueil.');
    }

    public function configureFields(string $pageName): iterable
    {
        yield ImageField::new('logoName', 'Logo')
            ->setBasePath('/uploads/partenaires')
            ->onlyOnIndex();

        yield TextField::new('nom', 'Nom du partenaire')->setColumns(8);

        yield TextField::new('logoFile', 'Logo')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false, 'image_uri' => true])
            ->setColumns(6)
            ->onlyOnForms()
            ->setHelp('Logo sur fond transparent (PNG ou SVG), hauteur minimale 120 px.');

        yield TextField::new('categorie', 'Catégorie')
            ->setColumns(6)
            ->setHelp('Ex. : Partenaire technique, Partenaire financier, Institution publique.');

        yield UrlField::new('siteWeb', 'Site web')->setColumns(6);

        yield TextareaField::new('description', 'Description')
            ->setColumns(12)
            ->setNumOfRows(4)
            ->hideOnIndex();

        yield IntegerField::new('ordre', 'Ordre d\'affichage')->setColumns(6);
        yield BooleanField::new('actif', 'Actif')->setColumns(6);
    }
}
