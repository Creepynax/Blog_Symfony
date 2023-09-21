<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isUpdate = $options['is_update']; // Utilisez 'is_update' ici

        $builder
            ->add('title', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Titre de l\'article'],
            ])
            ->add('summary', TextareaType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Résumé de l\'article'],
            ])
            ->add('content', TextareaType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Contenu de l\'article'],
            ])
            ->add('slug', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Slug de l\'article'],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image (fichier JPEG)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\File([
                        'maxSize' => '2040k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide.',
                    ])
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('save', SubmitType::class, [
                'label' => $isUpdate ? 'Modifier' : 'Créer', // Utilisez 'isUpdate' ici
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            'is_update' => false, // Ajoutez cette option
        ]);
    }
}
