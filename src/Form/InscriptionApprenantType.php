<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Apprenant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Création d'un compte apprenant (e-learning). On ne demande que le strict
 * nécessaire, avec un consentement explicite à l'usage des données.
 */
class InscriptionApprenantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['autocomplete' => 'given-name'],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['autocomplete' => 'family-name'],
                'help' => 'Écris ton nom comme il doit apparaître sur tes certificats.',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'attr' => ['autocomplete' => 'email', 'inputmode' => 'email'],
                'help' => 'Elle te sert à te connecter et à récupérer ton mot de passe.',
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['autocomplete' => 'tel', 'placeholder' => 'Facultatif'],
            ])
            ->add('motDePasse', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Les deux mots de passe ne sont pas identiques.',
                'first_options' => [
                    'label' => 'Mot de passe',
                    'help' => '8 caractères minimum.',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'second_options' => [
                    'label' => 'Confirme le mot de passe',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Choisis un mot de passe.'),
                    new Assert\Length(min: 8, max: 4096, minMessage: 'Ton mot de passe doit faire au moins {{ limit }} caractères.'),
                ],
            ])
            ->add('consentement', CheckboxType::class, [
                'label' => 'J\'accepte qu\'Amour&Vie conserve mon nom, mon adresse e-mail et mes résultats pour suivre ma progression et délivrer mes certificats.',
                'mapped' => false,
                'constraints' => [
                    new Assert\IsTrue(message: 'Ton accord est nécessaire pour créer un compte.'),
                ],
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
            'data_class' => Apprenant::class,
        ]);
    }
}
