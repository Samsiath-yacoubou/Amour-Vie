<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Emission;
use App\Enum\TypeEmission;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * Gestion des émissions radio & TV Amour&Vie : fichier audio hébergé pour
 * la radio, lien YouTube externe pour la TV.
 */
#[IsGranted('ROLE_GESTIONNAIRE')]
class EmissionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Emission::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Émission')
            ->setEntityLabelInPlural('Émissions')
            ->setPageTitle('index', 'Émissions radio & TV')
            ->setDefaultSort(['dateDiffusion' => 'DESC'])
            ->setSearchFields(['titre', 'description', 'animateurs'])
            ->setPaginatorPageSize(25)
            ->setHelp('index', 'Radio : ajoutez un fichier audio. TV : ajoutez un lien YouTube.');
    }

    public function configureActions(Actions $actions): Actions
    {
        $voirSurLeSite = Action::new('voirSurLeSite', 'Voir sur le site', 'fa fa-eye')
            ->linkToUrl(fn (Emission $emission) => $this->generateUrl('emission_detail', ['slug' => $emission->getSlug()]))
            ->setHtmlAttributes(['target' => '_blank'])
            ->displayIf(static fn (Emission $emission) => $emission->isPublie());

        return $actions
            ->add(Crud::PAGE_INDEX, $voirSurLeSite)
            ->add(Crud::PAGE_EDIT, $voirSurLeSite)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('type', 'Type')->setChoices(TypeEmission::choices()))
            ->add(EntityFilter::new('programme', 'Programme'))
            ->add(EntityFilter::new('thematiques', 'Thématique'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addTab('Contenu')->setIcon('fa fa-podcast');

        yield TextField::new('titre', 'Titre')->setColumns(12);

        yield ChoiceField::new('type', 'Type')
            ->setChoices(TypeEmission::choices())
            ->renderAsBadges([
                TypeEmission::RADIO->value => 'info',
                TypeEmission::TV->value => 'warning',
            ])
            ->setColumns(4);

        yield DateField::new('dateDiffusion', 'Date de diffusion')->setColumns(4);
        yield TextField::new('animateurs', 'Animateur·rice·s')->setColumns(4);

        yield TextareaField::new('description', 'Synopsis')
            ->setColumns(12)
            ->setNumOfRows(5)
            ->hideOnIndex();

        yield UrlField::new('urlExterne', 'Lien YouTube (émissions TV)')
            ->setColumns(12)
            ->setHelp('Pour une émission TV : lien YouTube. Au clic sur la couverture, le visiteur est envoyé vers YouTube dans un nouvel onglet.');

        yield FormField::addTab('Média & classement')->setIcon('fa fa-tags');

        yield TextField::new('imageFile', 'Couverture')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false, 'image_uri' => true])
            ->setColumns(6)
            ->onlyOnForms();

        yield TextField::new('fichierAudioFile', 'Fichier audio (émissions radio)')
            ->setFormType(VichFileType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => true])
            ->setColumns(6)
            ->onlyOnForms()
            ->setHelp('Pour une émission radio : le fichier MP3/WAV/OGG à écouter directement sur le site, au clic sur la couverture. Taille maximale : 40 Mo.');

        yield AssociationField::new('programme', 'Programme')->setColumns(6);
        yield AssociationField::new('thematiques', 'Thématiques')
            ->setColumns(12)
            ->autocomplete()
            ->hideOnIndex();

        yield IntegerField::new('ordre', "Ordre d'affichage")->setColumns(6)->hideOnIndex();
        yield BooleanField::new('publie', 'Publiée')->setColumns(6);
    }
}
