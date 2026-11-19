<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Utilisateur;
use App\Enum\RoleUtilisateur;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Gestion des comptes du back-office et de leurs rôles (§ 3.13).
 */
#[IsGranted('ROLE_SUPER_ADMIN')]
class UtilisateurCrudController extends AbstractCrudController
{
    public function __construct(private readonly UserPasswordHasherInterface $hacheur)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Utilisateur::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setPageTitle('index', 'Comptes My Amour&Vie')
            ->setDefaultSort(['nom' => 'ASC'])
            ->setSearchFields(['prenom', 'nom', 'email', 'fonction'])
            ->setHelp(
                'index',
                'Super administrateur : accès complet. Éditeur : publie les articles. '
                .'Gestionnaire : gère les contenus institutionnels. Contributeur : rédige sans publier.'
            );
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Identité')->setIcon('fa fa-user');

        yield TextField::new('prenom', 'Prénom')->setColumns(6);
        yield TextField::new('nom', 'Nom')->setColumns(6);
        yield EmailField::new('email', 'Adresse e-mail')
            ->setColumns(6)
            ->setHelp('Sert d\'identifiant de connexion.');
        yield TextField::new('fonction', 'Fonction')->setColumns(6)->hideOnIndex();

        yield FormField::addFieldset('Accès')->setIcon('fa fa-key');

        yield ChoiceField::new('roles', 'Rôles')
            ->setChoices(RoleUtilisateur::choices())
            ->allowMultipleChoices()
            ->renderExpanded()
            ->renderAsBadges([
                RoleUtilisateur::SUPER_ADMIN->value => 'danger',
                RoleUtilisateur::EDITEUR->value => 'primary',
                RoleUtilisateur::GESTIONNAIRE->value => 'info',
                RoleUtilisateur::CONTRIBUTEUR->value => 'secondary',
            ])
            ->setColumns(8);

        yield BooleanField::new('actif', 'Compte actif')
            ->setColumns(4)
            ->setHelp('Désactiver un compte empêche toute connexion sans supprimer les contenus rédigés.');

        // Le mot de passe n'est jamais affiché : il est saisi puis haché.
        yield TextField::new('motDePasseEnClair', 'Mot de passe')
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'help' => 'Au moins 12 caractères, avec majuscules, minuscules, chiffres et symboles.',
                ],
                'second_options' => ['label' => 'Confirmer le mot de passe'],
                'required' => Crud::PAGE_NEW === $pageName,
                'invalid_message' => 'Les deux mots de passe ne correspondent pas.',
            ])
            ->setColumns(8)
            ->onlyOnForms()
            ->setRequired(Crud::PAGE_NEW === $pageName);

        yield DateTimeField::new('derniereConnexion', 'Dernière connexion')->hideOnForm();
        yield DateTimeField::new('createdAt', 'Créé le')->onlyOnDetail();
    }

    public function createNewFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $formOptions,
        AdminContext $context,
    ): FormBuilderInterface {
        return $this->brancherHachage(parent::createNewFormBuilder($entityDto, $formOptions, $context));
    }

    public function createEditFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $formOptions,
        AdminContext $context,
    ): FormBuilderInterface {
        return $this->brancherHachage(parent::createEditFormBuilder($entityDto, $formOptions, $context));
    }

    /**
     * Hache le mot de passe saisi juste après la soumission du formulaire.
     * Un champ laissé vide en édition conserve le mot de passe existant.
     */
    private function brancherHachage(FormBuilderInterface $constructeur): FormBuilderInterface
    {
        return $constructeur->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $evenement): void {
            $utilisateur = $evenement->getData();

            if (!$utilisateur instanceof Utilisateur) {
                return;
            }

            $motDePasse = $utilisateur->getMotDePasseEnClair();

            if (null === $motDePasse || '' === $motDePasse) {
                return;
            }

            $utilisateur->setPassword($this->hacheur->hashPassword($utilisateur, $motDePasse));
            $utilisateur->eraseCredentials();
        });
    }
}
