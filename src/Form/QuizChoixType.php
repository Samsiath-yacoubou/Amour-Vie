<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\QuizChoix;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Choix de réponse d'une question de quiz, imbriqué dans QuizQuestionType.
 */
class QuizChoixType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, ['label' => 'Choix'])
            ->add('estCorrect', CheckboxType::class, ['label' => 'Bonne réponse', 'required' => false])
            ->add('ordre', IntegerType::class, ['label' => 'Ordre'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QuizChoix::class,
        ]);
    }
}
