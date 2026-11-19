<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Temoignage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire « Partage ton témoignage » du site public.
 * Pas de champ photo : une image peut être ajoutée en back-office après
 * consentement explicite recueilli hors-ligne.
 */
class TemoignageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudonyme', TextType::class, [
                'label' => 'Prénom ou pseudo',
                'required' => false,
                'attr' => ['placeholder' => 'Facultatif : tu peux rester anonyme'],
            ])
            ->add('age', IntegerType::class, [
                'label' => 'Âge',
                'required' => false,
                'attr' => ['placeholder' => 'Facultatif'],
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'required' => false,
                'attr' => ['placeholder' => 'Facultatif'],
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Ton témoignage',
                'attr' => ['placeholder' => 'Raconte ton expérience…', 'rows' => 8],
                'help' => 'Ton témoignage peut être publié anonymement sur le site après relecture par notre équipe.',
            ])
            // Champ leurre : invisible pour un humain, souvent rempli par les robots.
            ->add('siteWeb', TextType::class, [
                'label' => 'Ne pas remplir ce champ',
                'mapped' => false,
                'required' => false,
                'row_attr' => ['class' => 'pot-de-miel'],
                'attr' => ['tabindex' => '-1', 'autocomplete' => 'off'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Temoignage::class,
        ]);
    }
}
