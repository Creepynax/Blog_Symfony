<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EditProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('currentPassword', PasswordType::class, [
                'invalid_message' => "L'ancien mot de passe est invalide.",
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your current password',
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a new password',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Le mot de passe doit faire au moins {{ limit }} caractères.',
                        'max' => 4096,
                    ])
                ],
            ])
        ;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
