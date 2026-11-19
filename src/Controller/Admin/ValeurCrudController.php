<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Valeur;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Valeurs de l'organisation affichées sur « Qui sommes-nous ? » (§ 2.3).
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class ValeurCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Valeur::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Valeur')
            ->setEntityLabelInPlural('Nos valeurs')
            ->setDefaultSort(['ordre' => 'ASC'])
            ->setSearchFields(['titre', 'description']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('titre', 'Titre de la valeur')->setColumns(8);

        yield TextareaField::new('description', 'Description')
            ->setColumns(12)
            ->setNumOfRows(3)
            ->hideOnIndex();

        yield TextField::new('icone', 'Icône')
            ->setColumns(6)
            ->setHelp(
                'Nom d\'icône. Valeurs disponibles : handshake, shield, scale-balanced, heart, globe, '
                .'lightbulb, star, bullseye, flag, seedling, users, graduation-cap.'
            );

        yield ColorField::new('couleur', 'Couleur d\'accent')->setColumns(6)->hideOnIndex();

        yield IntegerField::new('ordre', 'Ordre')->setColumns(6);
        yield BooleanField::new('actif', 'Affichée')->setColumns(6);
    }
}
