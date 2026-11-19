<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Cours;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * Cours de l'e-learning. Un cours n'apparaît pour les jeunes qu'une fois
 * coché « Publié », après validation de son contenu par l'ABMS.
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class CoursCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Cours::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Cours')
            ->setEntityLabelInPlural('Cours')
            ->setPageTitle('index', 'Cours en ligne')
            ->setDefaultSort(['ordre' => 'ASC'])
            ->setSearchFields(['titre', 'resume'])
            ->setPaginatorPageSize(25);
    }

    public function configureActions(Actions $actions): Actions
    {
        $voirSurLeSite = Action::new('voirSurLeSite', 'Voir sur le site', 'fa fa-eye')
            ->linkToRoute('apprendre_cours', static fn (Cours $cours) => ['slug' => $cours->getSlug()])
            ->setHtmlAttributes(['target' => '_blank'])
            ->displayIf(static fn (Cours $cours) => $cours->isPublie());

        return $actions
            ->add(Crud::PAGE_INDEX, $voirSurLeSite)
            ->setPermission(Action::DELETE, 'ROLE_EDITEUR');
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addTab('Présentation')->setIcon('fa fa-graduation-cap');

        yield TextField::new('titre', 'Titre')->setColumns(12);

        yield TextareaField::new('resume', 'Résumé')
            ->setColumns(12)
            ->setNumOfRows(2)
            ->hideOnIndex()
            ->setHelp('300 caractères maximum. Affiché dans le catalogue.');

        yield TextEditorField::new('description', 'Description')
            ->setColumns(12)
            ->hideOnIndex()
            ->setNumOfRows(10)
            ->setHelp('À qui s\'adresse le cours, ce qu\'on y apprend.');

        yield TextField::new('imageFile', 'Image')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false, 'image_uri' => true])
            ->onlyOnForms()
            ->setColumns(6)
            ->setHelp('Image légère conseillée (moins de 300 Ko) : beaucoup de jeunes ont un petit forfait.');

        yield ImageField::new('imageName', 'Image')
            ->setBasePath('/uploads/cours')
            ->onlyOnIndex();

        yield FormField::addTab('Réglages')->setIcon('fa fa-sliders');

        yield ChoiceField::new('niveau', 'Niveau')->setChoices(Cours::NIVEAUX)->setColumns(4);
        yield IntegerField::new('dureeMinutes', 'Durée (minutes)')->setColumns(4);
        yield IntegerField::new('seuilReussite', 'Seuil de réussite (%)')
            ->setColumns(4)
            ->hideOnIndex()
            ->setHelp('Pourcentage de bonnes réponses pour valider un quiz de module et l\'examen final.');
        yield AssociationField::new('examenFinal', 'Examen final')
            ->setColumns(8)
            ->hideOnIndex()
            ->setHelp('Facultatif. Un quiz de la rubrique « Quiz » (décochez « Actif » pour le garder hors des quiz publics). Sa réussite conditionne le certificat.');
        yield IntegerField::new('tentativesExamenMax', 'Tentatives à l\'examen')
            ->setColumns(4)
            ->hideOnIndex()
            ->setHelp('0 = illimité.');
        yield AssociationField::new('programme', 'Programme')->setColumns(6)->hideOnIndex();
        yield IntegerField::new('ordre', 'Ordre dans le catalogue')->setColumns(6)->hideOnIndex();
        yield AssociationField::new('modules', 'Modules')->onlyOnIndex();
        yield BooleanField::new('publie', 'Publié')
            ->setColumns(12)
            ->setHelp('À cocher uniquement quand le contenu a été validé par l\'ABMS.');
    }
}
