<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\QuestionAnonyme;
use App\Entity\Utilisateur;
use App\Enum\StatutQuestion;
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
 * File de traitement des questions anonymes : réponse privée ou publication en FAQ.
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class QuestionAnonymeCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return QuestionAnonyme::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Question')
            ->setEntityLabelInPlural('Questions anonymes')
            ->setPageTitle('index', 'Questions anonymes des jeunes')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['pseudonyme', 'question', 'reponse'])
            ->setPaginatorPageSize(25)
            ->setHelp('index', 'Renseignez une réponse puis choisissez « Publier en FAQ » si elle peut aider d\'autres jeunes, ou « Répondre en privé » sinon.');
    }

    public function configureActions(Actions $actions): Actions
    {
        $publierFaq = Action::new('publierEnFaq', 'Publier en FAQ', 'fa fa-check-double')
            ->linkToCrudAction('publierEnFaq')
            ->displayIf(static fn (QuestionAnonyme $q) => StatutQuestion::PUBLIEE_FAQ !== $q->getStatut());

        $repondrePrivement = Action::new('repondrePrivement', 'Répondre en privé', 'fa fa-lock')
            ->linkToCrudAction('repondrePrivement')
            ->displayIf(static fn (QuestionAnonyme $q) => StatutQuestion::REPONDUE_PRIVEE !== $q->getStatut());

        $rejeter = Action::new('rejeter', 'Rejeter', 'fa fa-xmark')
            ->linkToCrudAction('rejeter')
            ->displayIf(static fn (QuestionAnonyme $q) => StatutQuestion::REJETEE !== $q->getStatut());

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $publierFaq)
            ->add(Crud::PAGE_DETAIL, $repondrePrivement)
            ->add(Crud::PAGE_DETAIL, $rejeter)
            ->add(Crud::PAGE_INDEX, $rejeter)
            // Les questions arrivent par le site public : pas de création manuelle.
            ->disable(Action::NEW)
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('statut', 'Statut')->setChoices(StatutQuestion::choices()));
    }

    public function configureFields(string $pageName): iterable
    {
        yield DateTimeField::new('createdAt', 'Reçue le')->hideOnForm();
        yield TextField::new('pseudonyme', 'Pseudo')->setColumns(4);

        yield TextareaField::new('question', 'Question')
            ->setNumOfRows(4)
            ->setColumns(12);

        yield TextareaField::new('reponse', 'Réponse')
            ->setNumOfRows(8)
            ->setColumns(12)
            ->setHelp('Rédigée par un·e pair-éducateur·rice. Visible publiquement seulement si le statut est « Publiée en FAQ ».');

        yield TextField::new('repondantLibre', 'Signature (pair-éducateur·rice)')->setColumns(6)->hideOnIndex();
        yield AssociationField::new('repondant', 'Compte répondant')->setColumns(6)->hideOnIndex();

        yield ChoiceField::new('statut', 'Statut')
            ->setChoices(StatutQuestion::choices())
            ->renderAsBadges([
                StatutQuestion::EN_ATTENTE->value => 'warning',
                StatutQuestion::REPONDUE_PRIVEE->value => 'info',
                StatutQuestion::PUBLIEE_FAQ->value => 'success',
                StatutQuestion::REJETEE->value => 'dark',
            ])
            ->setColumns(4);

        yield AssociationField::new('thematiques', 'Thématiques (FAQ)')
            ->setColumns(8)
            ->autocomplete()
            ->hideOnIndex();

        yield IntegerField::new('ordre', "Ordre d'affichage en FAQ")->setColumns(4)->hideOnIndex();
        yield DateTimeField::new('repondueAt', 'Répondue le')->onlyOnDetail();
    }

    #[AdminRoute(path: '/publier-en-faq', name: '_publier_en_faq')]
    public function publierEnFaq(AdminContext $context): RedirectResponse
    {
        return $this->changerStatut($context, StatutQuestion::PUBLIEE_FAQ);
    }

    #[AdminRoute(path: '/repondre-privement', name: '_repondre_privement')]
    public function repondrePrivement(AdminContext $context): RedirectResponse
    {
        return $this->changerStatut($context, StatutQuestion::REPONDUE_PRIVEE);
    }

    #[AdminRoute(path: '/rejeter', name: '_rejeter')]
    public function rejeter(AdminContext $context): RedirectResponse
    {
        return $this->changerStatut($context, StatutQuestion::REJETEE);
    }

    private function changerStatut(AdminContext $context, StatutQuestion $statut): RedirectResponse
    {
        $question = $context->getEntity()->getInstance();

        if ($question instanceof QuestionAnonyme) {
            $question->setStatut($statut);

            if (\in_array($statut, [StatutQuestion::REPONDUE_PRIVEE, StatutQuestion::PUBLIEE_FAQ], true)) {
                $question->setRepondueAt(new \DateTimeImmutable());

                $utilisateur = $this->getUser();
                if (null === $question->getRepondant() && $utilisateur instanceof Utilisateur) {
                    $question->setRepondant($utilisateur);
                }
            }

            $this->entityManager->flush();
        }

        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }
}
