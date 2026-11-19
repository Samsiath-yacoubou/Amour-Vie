<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Lecon;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Leçons des cours en ligne : un texte lisible seul, et éventuellement une
 * courte vidéo que le jeune choisit de charger.
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class LeconCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Lecon::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Leçon')
            ->setEntityLabelInPlural('Leçons')
            ->setPageTitle('index', 'Leçons des cours')
            ->setDefaultSort(['module' => 'ASC', 'ordre' => 'ASC'])
            ->setSearchFields(['titre', 'contenu', 'module.titre'])
            ->setPaginatorPageSize(50);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add(EntityFilter::new('module', 'Module'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('module', 'Module')->setColumns(6);
        yield IntegerField::new('ordre', 'Ordre dans le module')->setColumns(3);
        yield IntegerField::new('dureeMinutes', 'Durée (minutes)')->setColumns(3);
        yield TextField::new('titre', 'Titre')->setColumns(12);
        yield TextEditorField::new('contenu', 'Contenu de la leçon')
            ->setColumns(12)
            ->hideOnIndex()
            ->setNumOfRows(18)
            ->setHelp('Phrases courtes, paragraphes aérés : la leçon doit se lire facilement sur un téléphone.');
        yield UrlField::new('videoUrl', 'Vidéo (lien YouTube)')
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('Facultatif. Une vidéo courte (moins de 3 minutes). Elle ne se charge que si le jeune clique dessus.');
    }
}
