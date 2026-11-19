<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\NewsletterAbonne;
use App\Repository\NewsletterAbonneRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Liste des abonné·e·s à la newsletter. Collecte uniquement : pas d'envoi
 * de campagnes depuis ce contrôleur (MAILER_DSN local non configuré).
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class NewsletterAbonneCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return NewsletterAbonne::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Abonné·e')
            ->setEntityLabelInPlural('Abonnés newsletter')
            ->setPageTitle('index', 'Abonné·e·s à la newsletter')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['email', 'prenom'])
            ->setPaginatorPageSize(50);
    }

    public function configureActions(Actions $actions): Actions
    {
        $exporter = Action::new('exporterCsv', 'Exporter en CSV', 'fa fa-file-csv')
            ->linkToUrl(fn () => $this->generateUrl('admin_newsletter_export'))
            ->createAsGlobalAction();

        return $actions
            ->add(Crud::PAGE_INDEX, $exporter)
            // Les abonné·e·s s'inscrivent depuis le site public.
            ->disable(Action::NEW, Action::EDIT)
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('actif', 'Actif'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield DateTimeField::new('createdAt', 'Inscrit·e le')->hideOnForm();
        yield EmailField::new('email', 'E-mail');
        yield TextField::new('prenom', 'Prénom');
        yield BooleanField::new('actif', 'Actif');
    }

    #[Route('/admin/newsletter/export.csv', name: 'admin_newsletter_export', methods: ['GET'])]
    #[IsGranted('ROLE_GESTIONNAIRE')]
    public function exporterCsv(NewsletterAbonneRepository $newsletterAbonneRepository): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($newsletterAbonneRepository): void {
            $sortie = fopen('php://output', 'w');
            fputcsv($sortie, ['E-mail', 'Prénom', 'Inscrit·e le']);

            foreach ($newsletterAbonneRepository->trouverTousActifs() as $abonne) {
                fputcsv($sortie, [
                    $abonne->getEmail(),
                    $abonne->getPrenom(),
                    $abonne->getCreatedAt()?->format('d/m/Y'),
                ]);
            }

            fclose($sortie);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="newsletter-amour-et-vie.csv"');

        return $response;
    }
}
