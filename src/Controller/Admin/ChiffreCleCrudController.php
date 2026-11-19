<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\ChiffreCle;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Chiffres clés de la page d'accueil (§ 2.2.B et § 3.10).
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class ChiffreCleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ChiffreCle::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Chiffre clé')
            ->setEntityLabelInPlural('Chiffres clés')
            ->setDefaultSort(['ordre' => 'ASC'])
            ->setHelp('index', 'Ces indicateurs s\'affichent sous le bandeau principal de la page d\'accueil.');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('valeur', 'Valeur')
            ->setColumns(4)
            ->setHelp('Le nombre seul, sans texte. Il est animé au chargement de la page.');

        yield TextField::new('suffixe', 'Suffixe')
            ->setColumns(4)
            ->setHelp('Symbole affiché après le nombre, par exemple « + » ou « % ». Peut rester vide.');

        yield TextField::new('libelle', 'Libellé')
            ->setColumns(8)
            ->setHelp('Ex. : Années d\'engagement, Communautés touchées, Partenaires.');

        yield TextField::new('icone', 'Icône')
            ->setColumns(6)
            ->hideOnIndex()
            ->setHelp('Ex. : chart-simple, users, globe, handshake, calendar, graduation-cap.');

        yield IntegerField::new('ordre', 'Ordre')->setColumns(6);
        yield BooleanField::new('actif', 'Affiché')->setColumns(6);
    }
}
