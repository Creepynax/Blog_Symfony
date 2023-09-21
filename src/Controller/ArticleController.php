<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleForm;
use App\Repository\UserRepository;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    /**
     * @Route("/article/create", name="article_create")
     */
    public function create(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $article = new Article();

        // Utiliser l'ID utilisateur 7 par défaut
        $user = $userRepository->find(7);
        if (!$user) {
            throw new \Exception('Utilisateur avec ID = 7 non trouvé');
        }
        $article->setUser($user);

        $form = $this->createForm(ArticleForm::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Slugifier le titre
            $slugify = new Slugify();
            $article->setSlug($slugify->slugify($article->getTitle()));

            // Gérer le fichier téléchargé
            $file = $form['image']->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer l'exception si quelque chose se passe mal pendant le téléchargement du fichier
                }

                $article->setImage($newFilename);
            }

            $entityManager->persist($article);
            $entityManager->flush();

            // Ajouter un message Flash pour indiquer le succès
            $this->addFlash('success', 'Article créé avec succès.');

            return $this->redirectToRoute('article_create');
        }

        return $this->render('article/article.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
