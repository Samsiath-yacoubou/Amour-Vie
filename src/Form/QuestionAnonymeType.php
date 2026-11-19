<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\QuestionAnonyme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire « Pose ta question à un pair-éducateur » du site public.
 * Aucun champ e-mail : anonymat total, pas de suivi individuel possible.
 */
class QuestionAnonymeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudonyme', TextType::class, [
                'label' => 'Comment souhaites-tu être appelé·e ?',
                'required' => false,
                'attr' => ['placeholder' => 'Facultatif : tu peux rester anonyme'],
            ])
            ->add('question', TextareaType::class, [
                'label' => 'Ta question',
                'attr' => ['placeholder' => 'Pose ta question librement…', 'rows' => 6],
                'help' => 'Pose ta question en toute confidentialité. Un·e pair-éducateur·rice te répondra. Aucune information permettant de t\'identifier n\'est demandée.',
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
            'data_class' => QuestionAnonyme::class,
        ]);
    }
}
