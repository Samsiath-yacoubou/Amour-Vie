<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Elearning\GestionCertificats;
use App\Entity\Certificat;
use App\Repository\CertificatRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NullFilter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Certificats de l'e-learning. Ils sont délivrés automatiquement : l'équipe
 * ne peut ni en créer ni en modifier, seulement les consulter, les révoquer
 * ou les réémettre (ex. après correction du nom d'un jeune).
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class CertificatCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly GestionCertificats $certificats,
        private readonly CertificatRepository $certificatRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Certificat::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Certificat')
            ->setEntityLabelInPlural('Certificats')
            ->setPageTitle('index', 'Certificats délivrés')
            ->setDefaultSort(['delivreLe' => 'DESC'])
            ->setSearchFields(['numero', 'nomTitulaire', 'titreCours'])
            ->setPaginatorPageSize(50);
    }

    public function configureActions(Actions $actions): Actions
    {
        $telecharger = Action::new('telecharger', 'PDF', 'fa fa-file-pdf')
            ->linkToCrudAction('telecharger')
            ->setHtmlAttributes(['target' => '_blank']);

        $revoquer = Action::new('revoquer', 'Révoquer', 'fa fa-ban')
            ->linkToCrudAction('revoquer')
            ->displayIf(static fn (Certificat $certificat) => $certificat->estValide());

        $reemettre = Action::new('reemettre', 'Réémettre', 'fa fa-rotate')
            ->linkToCrudAction('reemettre');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $telecharger)
            ->add(Crud::PAGE_DETAIL, $telecharger)
            ->add(Crud::PAGE_DETAIL, $revoquer)
            ->add(Crud::PAGE_DETAIL, $reemettre)
            ->disable(Action::NEW, Action::EDIT)
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('cours', 'Cours'))
            ->add(NullFilter::new('revoqueLe', 'Statut')->setChoiceLabels('Valides', 'Révoqués'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('numero', 'Numéro');
        yield TextField::new('nomTitulaire', 'Titulaire');
        yield TextField::new('titreCours', 'Cours');
        yield IntegerField::new('scoreExamen', 'Examen final (%)')->hideOnIndex();
        yield DateTimeField::new('delivreLe', 'Délivré le')->setFormat('dd/MM/yyyy');
        yield BooleanField::new('valide', 'Valide')->renderAsSwitch(false)->setVirtual(true)
            ->formatValue(static fn ($valeur, Certificat $certificat) => $certificat->estValide());
        yield DateTimeField::new('reemisLe', 'Réémis le')->onlyOnDetail();
        yield DateTimeField::new('revoqueLe', 'Révoqué le')->onlyOnDetail();
        yield TextField::new('motifRevocation', 'Motif de révocation')->onlyOnDetail();
        yield AssociationField::new('apprenant', 'Compte apprenant')->onlyOnDetail();
    }

    #[AdminRoute('/{entityId}/pdf', name: 'telecharger')]
    public function telecharger(int $entityId): BinaryFileResponse
    {
        $certificat = $this->certificat($entityId);

        return $this->file(
            $this->certificats->cheminPdf($certificat),
            $this->certificats->nomFichier($certificat),
            ResponseHeaderBag::DISPOSITION_INLINE,
        );
    }

    #[AdminRoute('/{entityId}/revoquer', name: 'revoquer', options: ['methods' => ['GET', 'POST']])]
    public function revoquer(int $entityId, Request $request): Response
    {
        $certificat = $this->certificat($entityId);

        if ($request->isMethod('POST') && $this->isCsrfTokenValid('revoquer-'.$certificat->getId(), (string) $request->request->get('_token'))) {
            $this->certificats->revoquer($certificat, mb_substr(trim((string) $request->request->get('motif')), 0, 255));
            $this->addFlash('success', sprintf('Le certificat %s est révoqué : la page de vérification l\'indique désormais.', $certificat->getNumero()));

            return $this->redirectToRoute('admin_certificat_detail', ['entityId' => $certificat->getId()]);
        }

        return $this->render('admin/certificat_confirmation.html.twig', [
            'certificat' => $certificat,
            'action' => 'revoquer',
        ]);
    }

    #[AdminRoute('/{entityId}/reemettre', name: 'reemettre', options: ['methods' => ['GET', 'POST']])]
    public function reemettre(int $entityId, Request $request): Response
    {
        $certificat = $this->certificat($entityId);

        if ($request->isMethod('POST') && $this->isCsrfTokenValid('reemettre-'.$certificat->getId(), (string) $request->request->get('_token'))) {
            $this->certificats->reemettre($certificat);
            $this->addFlash('success', sprintf('Le certificat %s a été réémis au nom de %s.', $certificat->getNumero(), $certificat->getNomTitulaire()));

            return $this->redirectToRoute('admin_certificat_detail', ['entityId' => $certificat->getId()]);
        }

        return $this->render('admin/certificat_confirmation.html.twig', [
            'certificat' => $certificat,
            'action' => 'reemettre',
        ]);
    }

    /**
     * Paramètre de route simple ({entityId}) plutôt que la syntaxe mappée :
     * le menu « réglages » d'EasyAdmin régénère l'URL courante à partir des
     * paramètres de route, et doit y trouver entityId.
     */
    private function certificat(int $id): Certificat
    {
        return $this->certificatRepository->find($id)
            ?? throw $this->createNotFoundException('Ce certificat n\'existe pas.');
    }
}
