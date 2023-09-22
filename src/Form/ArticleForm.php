<?php

namespace App\Form;

use App\Entity\Article;
use Cocur\Slugify\Slugify;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class ArticleForm extends AbstractType
{
    private $slugify;

    public function __construct(Slugify $slugify)
    {
        $this->slugify = $slugify;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('summary', TextType::class)
            ->add('content', TextType::class)
            ->add('image', FileType::class, [
                'label' => 'Image (fichier JPEG)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\File([
                        'maxSize' => '2040k',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide',
                    ]),
                ],
            ])
            ->add('date', DateTimeType::class, ['widget' => 'single_text'])
            ->add('save', SubmitType::class, ['label' => 'Enregistrer']);

        // Ajouter l'événement POST_SUBMIT pour slugifier le titre
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $article = $event->getData();

            if ($article) {
                $slug = $this->slugify->slugify($article->getTitle());
                $article->setSlug($slug);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
