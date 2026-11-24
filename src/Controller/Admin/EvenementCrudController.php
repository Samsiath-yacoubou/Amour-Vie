<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Evenement;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * Gestion de l'agenda (§ 3.8).
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class EvenementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Evenement::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Événement')
            ->setEntityLabelInPlural('Événements')
            ->setPageTitle('index', 'Agenda')
            ->setDefaultSort(['dateDebut' => 'DESC'])
            ->setSearchFields(['titre', 'lieu', 'description']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $voirSurLeSite = Action::new('voirSurLeSite', 'Voir sur le site', 'fa fa-eye')
            ->linkToUrl(fn (Evenement $evenement) => $this->generateUrl('evenement_detail', ['slug' => $evenement->getSlug()]))
            ->setHtmlAttributes(['target' => '_blank'])
            ->displayIf(static fn (Evenement $evenement) => $evenement->isPublie());

        return $actions->add(Crud::PAGE_INDEX, $voirSurLeSite);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Événement')->setIcon('fa fa-calendar-days');

        yield TextField::new('titre', 'Titre')->setColumns(12);

        yield DateTimeField::new('dateDebut', 'Début')
            ->setColumns(6)
            ->setHelp('Date et heure de démarrage.');

        yield DateTimeField::new('dateFin', 'Fin')
            ->setColumns(6)
            ->hideOnIndex()
            ->setHelp('À renseigner pour un événement sur plusieurs jours.');

        yield TextField::new('lieu', 'Lieu')->setColumns(6);

        yield AssociationField::new('programme', 'Programme concerné')
            ->setColumns(6)
            ->hideOnIndex();

        yield TextEditorField::new('description', 'Description')
            ->setColumns(12)
            ->setNumOfRows(8)
            ->hideOnIndex();

        yield FormField::addFieldset('Visuel et diffusion')->setIcon('fa fa-share-nodes');

        yield ImageField::new('imageName', 'Image')
            ->setBasePath('uploads/evenements')
            ->onlyOnIndex();

        yield TextField::new('imageFile', 'Image de l\'événement')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false, 'image_uri' => true])
            ->setColumns(8)
            ->onlyOnForms();

        yield UrlField::new('lien', 'Lien d\'inscription')
            ->setColumns(8)
            ->hideOnIndex();

        yield BooleanField::new('publie', 'Publié')->setColumns(6);
    }
}
