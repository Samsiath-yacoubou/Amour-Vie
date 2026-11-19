<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\MessageContact;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Boîte de réception des messages du formulaire de contact (§ 2.8).
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class MessageContactCrudController extends AbstractCrudController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public static function getEntityFqcn(): string
    {
        return MessageContact::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Message')
            ->setEntityLabelInPlural('Messages reçus')
            ->setPageTitle('index', 'Messages du formulaire de contact')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['nom', 'prenom', 'email', 'sujet', 'message'])
            ->setPaginatorPageSize(25);
    }

    public function configureActions(Actions $actions): Actions
    {
        $repondre = Action::new('repondre', 'Répondre', 'fa fa-reply')
            ->linkToUrl(static fn (MessageContact $message) => sprintf(
                'mailto:%s?subject=%s',
                $message->getEmail(),
                rawurlencode('Re : '.$message->getSujet()),
            ));

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $repondre)
            ->add(Crud::PAGE_DETAIL, $repondre)
            // Les messages arrivent par le site public : pas de création manuelle.
            ->disable(Action::NEW)
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('lu', 'Lu'))
            ->add(BooleanFilter::new('traite', 'Traité'))
            ->add(DateTimeFilter::new('createdAt', 'Date de réception'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield DateTimeField::new('createdAt', 'Reçu le')->hideOnForm();
        yield TextField::new('nomComplet', 'Expéditeur')->onlyOnIndex();
        yield TextField::new('prenom', 'Prénom')->hideOnIndex();
        yield TextField::new('nom', 'Nom')->hideOnIndex();
        yield EmailField::new('email', 'Adresse e-mail');
        yield TextField::new('sujet', 'Sujet');

        yield TextareaField::new('message', 'Message')
            ->setNumOfRows(10)
            ->hideOnIndex();

        yield BooleanField::new('lu', 'Lu');
        yield BooleanField::new('traite', 'Traité');

        yield TextareaField::new('noteInterne', 'Note interne')
            ->setNumOfRows(4)
            ->hideOnIndex()
            ->setHelp('Visible uniquement dans le back-office.');

        yield TextField::new('adresseIp', 'Adresse IP')->onlyOnDetail();
    }

    /**
     * Consulter un message le marque automatiquement comme lu.
     */
    public function detail(AdminContext $context): Response
    {
        $message = $context->getEntity()->getInstance();

        if ($message instanceof MessageContact && !$message->isLu()) {
            $message->setLu(true);
            $this->entityManager->flush();
        }

        return parent::detail($context);
    }
}
