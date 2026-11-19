<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Temoignage;
use App\Enum\StatutModeration;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * File de modération des témoignages soumis par le formulaire public.
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class TemoignageCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Temoignage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Témoignage')
            ->setEntityLabelInPlural('Témoignages')
            ->setPageTitle('index', 'Témoignages soumis')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['pseudonyme', 'ville', 'contenu'])
            ->setPaginatorPageSize(25);
    }

    public function configureActions(Actions $actions): Actions
    {
        $publier = Action::new('publier', 'Publier', 'fa fa-check')
            ->linkToCrudAction('publier')
            ->displayIf(static fn (Temoignage $t) => StatutModeration::PUBLIE !== $t->getStatut());

        $rejeter = Action::new('rejeter', 'Rejeter', 'fa fa-xmark')
            ->linkToCrudAction('rejeter')
            ->displayIf(static fn (Temoignage $t) => StatutModeration::REJETE !== $t->getStatut());

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $publier)
            ->add(Crud::PAGE_DETAIL, $publier)
            ->add(Crud::PAGE_INDEX, $rejeter)
            ->add(Crud::PAGE_DETAIL, $rejeter)
            // Les témoignages arrivent par le site public : pas de création manuelle.
            ->disable(Action::NEW)
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('statut', 'Statut')->setChoices(StatutModeration::choices()));
    }

    public function configureFields(string $pageName): iterable
    {
        yield DateTimeField::new('createdAt', 'Reçu le')->hideOnForm();
        yield TextField::new('pseudonyme', 'Pseudo')->setColumns(4);
        yield IntegerField::new('age', 'Âge')->setColumns(2)->hideOnIndex();
        yield TextField::new('ville', 'Ville')->setColumns(3)->hideOnIndex();

        yield TextareaField::new('contenu', 'Témoignage')
            ->setNumOfRows(8)
            ->setColumns(12);

        yield ChoiceField::new('statut', 'Statut')
            ->setChoices(StatutModeration::choices())
            ->renderAsBadges([
                StatutModeration::EN_ATTENTE->value => 'warning',
                StatutModeration::PUBLIE->value => 'success',
                StatutModeration::REJETE->value => 'dark',
            ])
            ->setColumns(4);

        yield AssociationField::new('programme', 'Programme')->setColumns(4)->hideOnIndex();

        yield TextareaField::new('noteInterne', 'Note interne')
            ->setNumOfRows(4)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('Visible uniquement dans le back-office.');

        yield TextField::new('adresseIp', 'Adresse IP')->onlyOnDetail();
    }

    #[AdminRoute(path: '/publier', name: '_publier')]
    public function publier(AdminContext $context): RedirectResponse
    {
        return $this->changerStatut($context, StatutModeration::PUBLIE);
    }

    #[AdminRoute(path: '/rejeter', name: '_rejeter')]
    public function rejeter(AdminContext $context): RedirectResponse
    {
        return $this->changerStatut($context, StatutModeration::REJETE);
    }

    private function changerStatut(AdminContext $context, StatutModeration $statut): RedirectResponse
    {
        $temoignage = $context->getEntity()->getInstance();

        if ($temoignage instanceof Temoignage) {
            $temoignage->setStatut($statut);
            $this->entityManager->flush();
        }

        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }
}
