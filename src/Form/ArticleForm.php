<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ArticleForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [
                    new Assert\Length(['max' => 255])
                ]
            ])
            ->add('summary', TextType::class, [
                'constraints' => [
                    new Assert\Length(['max' => 500])
                ]
            ])
            ->add('content', TextType::class, [
                'constraints' => [
                    new Assert\Length(['max' => 5000])
                ]
            ])
            ->add('slug', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('image', FileType::class, [
                'label' => 'Image (fichier JPEG)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '2040k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide.',
                    ])
                ],
            ])
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text'
            ])
            ->add('tags', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Tags (séparés par des virgules)',
            ])
            ->add('addTag', SubmitType::class, [
                'label' => 'Ajouter un Tag',
                'attr' => [
                    'class' => 'add-tag-button',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Créer un article'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}

