<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Galerie;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Galeries de la médiathèque (§ 3.11).
 */
class GalerieCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Galerie::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Galerie')
            ->setEntityLabelInPlural('Galeries')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['nom', 'description'])
            ->setHelp('index', 'Créez d\'abord la galerie, puis rattachez-lui des médias depuis la rubrique « Médiathèque ».');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('nom', 'Nom de la galerie')->setColumns(8);

        yield TextareaField::new('description', 'Description')
            ->setColumns(8)
            ->setNumOfRows(3)
            ->hideOnIndex();

        yield AssociationField::new('medias', 'Médias')
            ->onlyOnIndex()
            ->formatValue(static fn ($value, Galerie $galerie) => \count($galerie->getMedias()).' média(s)');
    }
}
