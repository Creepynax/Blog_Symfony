<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
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
        return $this->render('home/home.html.twig', [
            'controller_name' => 'Bienvenue sur le Blog HACH',
            'pagination' => $pagination,

        ]);
    }
}
