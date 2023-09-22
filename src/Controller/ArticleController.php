<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\ArticleRepository;

#[Route('/article')]
class ArticleController extends AbstractController
{
    #[Route('/list', name: 'article_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, Request $request, PaginatorInterface $paginator): Response
    {
        // Utilisez Doctrine pour récupérer tous les articles depuis la base de données
        $articleRepository = $entityManager->getRepository(Article::class);

        // Créez une requête Doctrine pour récupérer tous les articles
        $query = $articleRepository->createQueryBuilder('a')
            ->getQuery();

        // Paginez les résultats avec le composant KnpPaginatorBundle
        $pagination = $paginator->paginate(
            $query, // Requête Doctrine
            $request->query->getInt('page', 1), // Numéro de page
            5 // Nombre d'articles par page
        );

        // Rend la vue listant tous les articles avec pagination
        return $this->render('article/list.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/{id}', name: 'article_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Article $article): Response
    {
        // Chargez la relation tags en utilisant la méthode getTags
        $article->getTags();

        // Rend la vue affichant un article spécifique
        return $this->render('article/index.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/new', name: 'article_new', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_USER")] // Assurez-vous que l'utilisateur est authentifié pour créer un article
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Obtenez l'utilisateur actuellement connecté (assurez-vous d'avoir la configuration de sécurité appropriée)
        $user = $this->getUser();

        // Crée une nouvelle instance de la classe Article
        $article = new Article();

        // Attribuez l'utilisateur à l'article
        $article->setUser($user);

        // Créez un formulaire pour l'entité Article
        $form = $this->createForm(ArticleType::class, $article);

        // Traite la soumission du formulaire
        $form->handleRequest($request);

        // Vérifie si le formulaire a été soumis et est valide
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

            // Redirige vers la liste des articles après la création
            return $this->redirectToRoute('article_index');
        }

        // Rend la vue pour créer un nouvel article
        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/update/{id}', name: 'article_update', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_USER")]
    public function update(Request $request, int $id, ArticleRepository $articleRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'article par son ID
        $article = $articleRepository->find($id);
    
        if (!$article) {
            throw $this->createNotFoundException("L'article avec l'ID $id n'existe pas.");
        }
    
        // Déterminez si vous êtes en mode mise à jour
        $isUpdate = true;
 
        // Créer le formulaire de modification
        $form = $this->createForm(ArticleType::class, $article, [
            'is_update' => $isUpdate, // Passez 'is_update' comme option au formulaire
        ]);
    
        // Traiter la soumission du formulaire
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Aucun besoin de persister l'article car il est déjà géré par Doctrine
            $entityManager->flush();
    
            $this->addFlash('success', 'Article modifié avec succès.');
            return $this->redirectToRoute('article_index');
        }
  
        return $this->render('article/update.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'article_delete', methods: ['POST'])]
    #[IsGranted("ROLE_USER")]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        // Vérifiez si le jeton CSRF est valide (pour éviter les attaques CSRF)
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            // Supprimez l'article de la base de données
            $entityManager->remove($article);
            $entityManager->flush();

        // Ajoutez un message Flash pour indiquer le succès de la suppression
        $this->addFlash('success', 'Article supprimé avec succès.');
        } else {
        // Ajoutez un message Flash en cas de jeton CSRF invalide
        $this->addFlash('error', 'Échec de la suppression de l\'article. Jeton CSRF invalide.');
        }

        // Redirigez vers la liste des articles après la suppression
        return $this->redirectToRoute('article_index');
    }

}
