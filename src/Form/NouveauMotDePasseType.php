<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Choix d'un nouveau mot de passe après un lien de réinitialisation.
 */
class NouveauMotDePasseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('motDePasse', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => 'Les deux mots de passe ne sont pas identiques.',
            'first_options' => [
                'label' => 'Nouveau mot de passe',
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
        ]);
    }
}
