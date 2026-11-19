<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\TypePublication;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Types de documents de la bibliothèque numérique (§ 2.6 et § 5.1).
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class TypePublicationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TypePublication::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Type de publication')
            ->setEntityLabelInPlural('Types de publication')
            ->setDefaultSort(['ordre' => 'ASC'])
            ->setSearchFields(['nom']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('nom', 'Libellé')
            ->setColumns(8)
            ->setHelp('Ex. : Rapport, Étude, Guide, Note de politique, Brochure, Newsletter.');

        yield TextField::new('icone', 'Icône')
            ->setColumns(6)
            ->hideOnIndex()
            ->setHelp('Ex. : file-lines, book, newspaper, chart-simple.');

        yield ColorField::new('couleur', 'Couleur')->setColumns(6)->hideOnIndex();
        yield IntegerField::new('ordre', 'Ordre')->setColumns(6);
    }
}
