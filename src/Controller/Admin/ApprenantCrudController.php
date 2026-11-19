<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Apprenant;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Comptes des jeunes inscrits à l'e-learning. Données de mineurs possibles :
 * consultation par les gestionnaires, aucune création depuis l'admin ; seuls
 * le nom (correction d'une faute) et l'activation se modifient ; suppression
 * réservée aux éditeurs (demande de suppression d'un jeune).
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class ApprenantCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Apprenant::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Apprenant')
            ->setEntityLabelInPlural('Apprenants')
            ->setPageTitle('index', 'Apprenants inscrits')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['prenom', 'nom', 'email'])
            ->setPaginatorPageSize(50);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW)
            ->setPermission(Action::DELETE, 'ROLE_EDITEUR');
    }

    public function configureFields(string $pageName): iterable
    {
        // Prénom et nom modifiables pour corriger une faute signalée par le
        // jeune (puis réémettre son certificat) ; le reste est en lecture seule.
        yield TextField::new('prenom', 'Prénom')
            ->setHelp('À corriger uniquement à la demande du jeune, puis réémettre son certificat.');
        yield TextField::new('nom', 'Nom');
        yield EmailField::new('email', 'E-mail')->setFormTypeOption('disabled', true);
        yield TextField::new('telephone', 'Téléphone')->hideOnIndex()->setFormTypeOption('disabled', true);
        yield AssociationField::new('inscriptions', 'Cours suivis')->onlyOnIndex();
        yield DateTimeField::new('createdAt', 'Inscrit le')->hideOnForm();
        yield DateTimeField::new('derniereConnexion', 'Dernière connexion')->hideOnForm();
        yield DateTimeField::new('consentementAt', 'Consentement donné le')->onlyOnDetail();
        yield BooleanField::new('actif', 'Compte actif')
            ->setHelp('Décoche pour bloquer la connexion de ce compte.');
    }
}
