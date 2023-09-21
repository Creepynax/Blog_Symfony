<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleForm;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security; // Ajouter ceci en haut du fichier

class ArticleController extends AbstractController
{
    private $security; // Ajoutez cette propriété dans la classe ArticleController

    // Mettez à jour le constructeur de la classe pour injecter la dépendance
    public function __construct(Security $security) {
        $this->security = $security;
    }

    public function showByUser(int $userId, ArticleRepository $articleRepository, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($userId);
        if (!$user) {
            throw new \Exception('Utilisateur avec ID = ' . $userId . ' non trouvé');
        }
        $articles = $articleRepository->getArticlesByUserId($user);

        return $this->render('article/list.html.twig', [
            'articles' => $articles,
        ]);
    }

    public function create(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $article = new Article();

        // Utilisez l'ID utilisateur 7 par défaut
        $user = $userRepository->find(7);
        if (!$user) {
            throw new \Exception('Utilisateur avec ID = 7 non trouvé');
        }
        $article->setUser($user);

        $form = $this->createForm(ArticleForm::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

    public function showMyArticles(ArticleRepository $articleRepository): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login'); // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
        }
        $articles = $articleRepository->getArticlesByUserId($user);

        return $this->render('article/list.html.twig', [
            'articles' => $articles,
        ]);
    }

    // Ajoutez cette méthode dans ArticleController
    public function delete(int $id, EntityManagerInterface $entityManager, ArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->find($id);
        if (!$article) {
           throw $this->createNotFoundException('L\'article demandé n\'existe pas');
        }

        $entityManager->remove($article);
        $entityManager->flush();

        $this->addFlash('success', 'Article supprimé avec succès.');

        return $this->redirectToRoute('my_articles');
    }

    public function edit(int $id, Request $request, EntityManagerInterface $entityManager, ArticleRepository $articleRepository): Response
    {
    $article = $articleRepository->find($id);
    if (!$article) {
        throw $this->createNotFoundException('L\'article demandé n\'existe pas');
    }

    $form = $this->createForm(ArticleForm::class, $article);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($article);
        $entityManager->flush();

        $this->addFlash('success', 'Article modifié avec succès.');
        return $this->redirectToRoute('my_articles');
    }

    return $this->render('article/edit.html.twig', [
        'form' => $form->createView(),
    ]);
}

}



