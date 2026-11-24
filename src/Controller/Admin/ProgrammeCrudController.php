<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Programme;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * Gestion des programmes (§ 3.4).
 * L'équipe peut créer de nouveaux programmes sans intervention technique (§ 5.1).
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class ProgrammeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Programme::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Programme')
            ->setEntityLabelInPlural('Programmes')
            ->setPageTitle('index', 'Nos programmes')
            ->setDefaultSort(['ordre' => 'ASC'])
            ->setSearchFields(['nom', 'sigle', 'descriptionCourte'])
            ->setHelp('index', 'L\'ordre d\'affichage détermine la position du programme sur la page d\'accueil et dans le menu.');
    }

    public function configureActions(Actions $actions): Actions
    {
        $voirSurLeSite = Action::new('voirSurLeSite', 'Voir sur le site', 'fa fa-eye')
            ->linkToUrl(fn (Programme $programme) => $this->generateUrl('programme_detail', ['slug' => $programme->getSlug()]))
            ->setHtmlAttributes(['target' => '_blank'])
            ->displayIf(static fn (Programme $programme) => $programme->isActif());

        return $actions
            ->add(Crud::PAGE_INDEX, $voirSurLeSite)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN');
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addTab('Identité')->setIcon('fa fa-id-card');

        yield TextField::new('sigle', 'Sigle')
            ->setColumns(4)
            ->setHelp('Ex. : RCDR, WEBnet, EPNV, WOR.');

        yield TextField::new('nom', 'Nom complet du programme')->setColumns(8);

        yield TextareaField::new('descriptionCourte', 'Description courte')
            ->setColumns(12)
            ->setNumOfRows(3)
            ->hideOnIndex()
            ->setHelp('Affichée sur la carte du programme en page d\'accueil. 400 caractères maximum.');

        yield ColorField::new('couleur', 'Couleur du programme')
            ->setColumns(4)
            ->hideOnIndex()
            ->setHelp('Utilisée pour l\'accent visuel des cartes et de la page dédiée.');

        yield IntegerField::new('ordre', 'Ordre d\'affichage')->setColumns(4);

        yield BooleanField::new('actif', 'Actif')->setColumns(4);

        yield FormField::addTab('Visuels')->setIcon('fa fa-image');

        yield ImageField::new('logoName', 'Logo')
            ->setBasePath('uploads/programmes')
            ->onlyOnIndex();

        yield TextField::new('logoFile', 'Logo du programme')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false, 'image_uri' => true])
            ->setColumns(6)
            ->onlyOnForms()
            ->setHelp('Format carré, fond transparent de préférence (PNG ou SVG).');

        yield TextField::new('imageFile', 'Image principale')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false, 'image_uri' => true])
            ->setColumns(6)
            ->onlyOnForms()
            ->setHelp('Photo d\'illustration affichée en haut de la page du programme.');

        yield AssociationField::new('galerie', 'Galerie photos')
            ->setColumns(12)
            ->onlyOnForms()
            ->autocomplete();

        yield FormField::addTab('Contenu détaillé')->setIcon('fa fa-align-left');

        yield TextEditorField::new('description', 'Description générale')
            ->setColumns(12)->hideOnIndex()->setNumOfRows(10);

        yield TextEditorField::new('objectifs', 'Objectifs')
            ->setColumns(12)->hideOnIndex()->setNumOfRows(8);

        yield TextEditorField::new('activites', 'Activités principales')
            ->setColumns(12)->hideOnIndex()->setNumOfRows(8);

        yield TextEditorField::new('resultats', 'Résultats et impact')
            ->setColumns(12)->hideOnIndex()->setNumOfRows(8);

        yield FormField::addTab('Référencement')->setIcon('fa fa-magnifying-glass');

        yield TextField::new('seoTitre', 'Titre SEO')->setColumns(8)->onlyOnForms();
        yield TextareaField::new('seoDescription', 'Méta-description')
            ->setColumns(8)->setNumOfRows(2)->onlyOnForms();
    }
}
