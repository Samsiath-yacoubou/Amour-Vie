<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\TypeArticle;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Types éditoriaux : actualité, communiqué, analyse, interview… (§ 5.1).
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class TypeArticleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TypeArticle::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Type d\'article')
            ->setEntityLabelInPlural('Types d\'article')
            ->setDefaultSort(['ordre' => 'ASC'])
            ->setSearchFields(['nom'])
            ->setHelp('index', 'Ajoutez ici tout nouveau format éditorial sans intervention technique.');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('nom', 'Libellé')->setColumns(8);
        yield ColorField::new('couleur', 'Couleur')->setColumns(6)->hideOnIndex();
        yield IntegerField::new('ordre', 'Ordre')->setColumns(6);
    }
}
