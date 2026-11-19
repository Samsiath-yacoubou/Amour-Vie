<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Quiz;
use App\Form\QuizQuestionType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Gestion des quiz pédagogiques. Le score est calculé côté client : ce
 * contrôleur ne gère que le contenu (questions/choix), jamais de tentatives.
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class QuizCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Quiz::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Quiz')
            ->setEntityLabelInPlural('Quiz')
            ->setPageTitle('index', 'Quiz pédagogiques')
            ->setDefaultSort(['ordre' => 'ASC'])
            ->setSearchFields(['titre', 'description'])
            ->setPaginatorPageSize(25);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addTab('Contenu')->setIcon('fa fa-circle-question');

        yield TextField::new('titre', 'Titre')->setColumns(12);

        yield TextareaField::new('description', 'Introduction')
            ->setColumns(12)
            ->setNumOfRows(3)
            ->hideOnIndex();

        yield AssociationField::new('programme', 'Programme')->setColumns(6)->hideOnIndex();
        yield AssociationField::new('thematiques', 'Thématiques')
            ->setColumns(6)
            ->autocomplete()
            ->hideOnIndex();

        yield IntegerField::new('ordre', "Ordre d'affichage")->setColumns(4)->hideOnIndex();
        yield BooleanField::new('actif', 'Actif')->setColumns(4);

        yield FormField::addTab('Questions')->setIcon('fa fa-list-ol')->onlyOnForms();

        yield CollectionField::new('questions', 'Questions')
            ->onlyOnForms()
            ->setColumns(12)
            ->setEntryType(QuizQuestionType::class)
            ->allowAdd()
            ->allowDelete()
            ->renderExpanded()
            ->setHelp('Chaque question a plusieurs choix ; coche « Bonne réponse » sur le choix correct.');
    }
}
