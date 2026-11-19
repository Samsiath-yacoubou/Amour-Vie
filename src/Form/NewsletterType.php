<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\NewsletterAbonne;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire d'inscription à la newsletter (footer du site public).
 */
class NewsletterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'attr' => ['placeholder' => 'vous@exemple.org', 'autocomplete' => 'email'],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
                'attr' => ['placeholder' => 'Votre prénom (facultatif)', 'autocomplete' => 'given-name'],
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
            'data_class' => NewsletterAbonne::class,
        ]);
    }
}
