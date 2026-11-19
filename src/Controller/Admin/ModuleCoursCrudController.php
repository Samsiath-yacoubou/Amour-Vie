<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ModuleCours;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Modules des cours en ligne. Le quiz de validation se choisit parmi les
 * quiz de la rubrique « Quiz » (questions, choix et explications).
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class ModuleCoursCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ModuleCours::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Module')
            ->setEntityLabelInPlural('Modules')
            ->setPageTitle('index', 'Modules des cours')
            ->setDefaultSort(['cours' => 'ASC', 'ordre' => 'ASC'])
            ->setSearchFields(['titre', 'cours.titre'])
            ->setPaginatorPageSize(50);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add(EntityFilter::new('cours', 'Cours'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('cours', 'Cours')->setColumns(6);
        yield IntegerField::new('ordre', 'Ordre dans le cours')->setColumns(6);
        yield TextField::new('titre', 'Titre')->setColumns(12);
        yield TextareaField::new('description', 'Description courte')
            ->setColumns(12)
            ->setNumOfRows(2)
            ->hideOnIndex();
        yield AssociationField::new('quiz', 'Quiz de validation')
            ->setColumns(12)
            ->setHelp('Facultatif. Crée d\'abord le quiz dans « Quiz » (décoche « Actif » pour qu\'il n\'apparaisse pas aussi dans les quiz publics). Sans quiz, le module est validé dès que toutes ses leçons sont lues.');
        yield AssociationField::new('lecons', 'Leçons')->onlyOnIndex();
    }
}
