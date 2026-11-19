<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\QuizQuestion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Question d'un quiz, imbriquée dans le formulaire d'édition du Quiz.
 */
class QuizQuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enonce', TextareaType::class, ['label' => 'Énoncé', 'attr' => ['rows' => 2]])
            ->add('explication', TextareaType::class, [
                'label' => 'Explication (affichée après réponse)',
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('ordre', IntegerType::class, ['label' => 'Ordre'])
            ->add('choix', CollectionType::class, [
                'label' => 'Choix de réponse',
                'entry_type' => QuizChoixType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QuizQuestion::class,
        ]);
    }
}
