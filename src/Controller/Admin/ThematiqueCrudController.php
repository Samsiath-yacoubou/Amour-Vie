<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Thematique;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Thématiques transversales, librement extensibles par l'équipe (§ 5.1).
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class ThematiqueCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Thematique::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Thématique')
            ->setEntityLabelInPlural('Thématiques')
            ->setDefaultSort(['ordre' => 'ASC'])
            ->setSearchFields(['nom', 'description'])
            ->setHelp('index', 'Les thématiques alimentent les filtres des rubriques « Actualités » et « Publications ».');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('nom', 'Nom')->setColumns(8);

        yield TextareaField::new('description', 'Description')
            ->setColumns(8)->setNumOfRows(2)->hideOnIndex();

        yield ColorField::new('couleur', 'Couleur')->setColumns(6)->hideOnIndex();
        yield IntegerField::new('ordre', 'Ordre')->setColumns(6);
    }
}
