<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Elearning\ProgressionCours;
use App\Entity\InscriptionCours;
use App\Repository\InscriptionCoursRepository;
use App\Repository\TentativeExamenRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NullFilter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Suivi des apprenants : qui suit quel cours, et où il en est. Lecture
 * seule ; seule action possible : redonner ses tentatives d'examen à un
 * jeune (ex. après un problème technique).
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class InscriptionCoursCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly ProgressionCours $progression,
        private readonly TentativeExamenRepository $tentativeExamenRepository,
        private readonly InscriptionCoursRepository $inscriptionRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return InscriptionCours::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Inscription')
            ->setEntityLabelInPlural('Suivi des apprenants')
            ->setPageTitle('index', 'Suivi des apprenants')
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setSearchFields(['apprenant.prenom', 'apprenant.nom', 'apprenant.email', 'cours.titre'])
            ->setPaginatorPageSize(50);
    }

    public function configureActions(Actions $actions): Actions
    {
        $reinitialiser = Action::new('reinitialiserExamen', 'Redonner les tentatives d\'examen', 'fa fa-rotate-left')
            ->linkToCrudAction('reinitialiserExamen')
            ->displayIf(static fn (InscriptionCours $inscription) => $inscription->getCours()->aUnExamenFinal());

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $reinitialiser)
            ->disable(Action::NEW, Action::EDIT, Action::DELETE, Action::BATCH_DELETE);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('cours', 'Cours'))
            ->add(NullFilter::new('termineeAt', 'État')->setChoiceLabels('En cours', 'Terminé'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('apprenant', 'Apprenant');
        yield AssociationField::new('cours', 'Cours');
        yield TextField::new('progression', 'Progression')
            ->setVirtual(true)
            ->formatValue(fn ($valeur, InscriptionCours $inscription) => $this->resumeProgression($inscription));
        yield DateTimeField::new('createdAt', 'Inscrit le')->setFormat('dd/MM/yyyy');
        yield DateTimeField::new('updatedAt', 'Dernière activité')->setFormat('dd/MM/yyyy HH:mm');
        yield DateTimeField::new('termineeAt', 'Terminé le')->setFormat('dd/MM/yyyy');
        yield AssociationField::new('derniereLecon', 'Dernière leçon ouverte')->onlyOnDetail();
    }

    #[AdminRoute('/{entityId}/reinitialiser-examen', name: 'reinitialiser_examen', options: ['methods' => ['GET', 'POST']])]
    public function reinitialiserExamen(int $entityId, Request $request): Response
    {
        // Paramètre simple {entityId} : EasyAdmin régénère l'URL courante avec.
        $inscription = $this->inscriptionRepository->find($entityId)
            ?? throw $this->createNotFoundException('Cette inscription n\'existe pas.');

        if ($request->isMethod('POST') && $this->isCsrfTokenValid('reinitialiser-examen-'.$inscription->getId(), (string) $request->request->get('_token'))) {
            $nombre = $this->tentativeExamenRepository->supprimerPour($inscription->getApprenant(), $inscription->getCours());
            $this->addFlash('success', sprintf('%d tentative(s) d\'examen effacée(s) : %s peut repasser l\'examen.', $nombre, $inscription->getApprenant()->getNomComplet()));

            return $this->redirectToRoute('admin_inscription_cours_detail', ['entityId' => $inscription->getId()]);
        }

        return $this->render('admin/inscription_reinitialiser_examen.html.twig', [
            'inscription' => $inscription,
            'bilan' => $this->progression->calculer($inscription->getApprenant(), $inscription->getCours()),
        ]);
    }

    private function resumeProgression(InscriptionCours $inscription): string
    {
        $bilan = $this->progression->calculer($inscription->getApprenant(), $inscription->getCours());
        $texte = $bilan->getPourcentage().' %';

        if (null !== $bilan->examen && $bilan->examen['tentatives'] > 0) {
            $texte .= sprintf(', examen : %s (%d tentative%s)',
                $bilan->examen['reussi'] ? 'réussi '.$bilan->examen['meilleur'].' %' : 'meilleur '.$bilan->examen['meilleur'].' %',
                $bilan->examen['tentatives'],
                $bilan->examen['tentatives'] > 1 ? 's' : '',
            );
        }

        return $texte;
    }
}
