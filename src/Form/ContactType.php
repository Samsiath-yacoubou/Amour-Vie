<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\MessageContact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire de contact du site public (§ 2.8).
 */
class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['placeholder' => 'Votre prénom', 'autocomplete' => 'given-name'],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Votre nom', 'autocomplete' => 'family-name'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'attr' => ['placeholder' => 'vous@exemple.org', 'autocomplete' => 'email'],
            ])
            ->add('sujet', TextType::class, [
                'label' => 'Sujet',
                'attr' => ['placeholder' => 'Objet de votre message'],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => ['placeholder' => 'Décrivez votre demande…', 'rows' => 7],
                'help' => 'Merci de ne pas transmettre d\'informations sensibles via ce formulaire.',
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
            'data_class' => MessageContact::class,
        ]);
    }
}
