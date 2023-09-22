<?php

// src/Controller/TagController.php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Article;
use App\Form\TagType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TagRepository;




#[Route('/tag')]
class TagController extends AbstractController
{
    #[Route('/list', name: 'tag_index', methods: ['GET'])]
    public function index(EntityManagerInterface $doctrine): Response
    {
        $tags = $doctrine->getRepository(Tag::class)->findAll();

        return $this->render('tag/index.html.twig', [
            'tags' => $tags,
        ]);
    }

    #[Route('/{id}', name: 'tag_show', methods: ['GET'], condition: "params['id'] < 1000", requirements: ['id' => '\d+'])]
    public function show(Tag $tag): Response
    {
        return $this->render('tag/show.html.twig', [
            'tag' => $tag,
        ]);
    }

    #[Route('/new', name: 'tag_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    // Crée une nouvelle instance de la classe Tag
    $tag = new Tag();

    // Crée un formulaire pour l'entité Tag en utilisant TagType
    $form = $this->createForm(TagType::class, $tag);

    // Traite la soumission du formulaire
    $form->handleRequest($request);

    // Vérifie si le formulaire a été soumis et est valide
    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($tag);
        $entityManager->flush();

        // Ajouter un message Flash pour indiquer le succès
        $this->addFlash('success', 'Tag créé avec succès.');

        // Redirige vers la liste des tags après la création
        return $this->redirectToRoute('tag_index');
    }

    // Rend la vue pour créer un nouveau tag en utilisant le formulaire TagType
    return $this->render('tag/new.html.twig', [
        'tag' => $tag,
        'form' => $form->createView(),
    ]);
}
#[Route('/update/{id}', name: 'tag_update', methods: ['GET', 'POST'])]
public function update(Request $request, Tag $tag, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(TagType::class, $tag);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush(); // Utilisez le gestionnaire d'entité correct

        return $this->redirectToRoute('tag_index');
    }

    return $this->render('tag/update.html.twig', [
        'tag' => $tag,
        'form' => $form->createView(),
    ]);
}


#[Route('/delete/{id}', name: 'tag_delete', methods: ['POST'])]
public function delete(Request $request, Tag $tag, EntityManagerInterface $entityManager): Response
{
    // Vérifiez si le jeton CSRF est valide (pour éviter les attaques CSRF)
    if ($this->isCsrfTokenValid('delete' . $tag->getId(), $request->request->get('_token'))) {
        // Supprimez l'entité Tag de la base de données
        $entityManager->remove($tag);
        $entityManager->flush();

        // Ajoutez un message Flash pour indiquer le succès de la suppression
        $this->addFlash('success', 'Tag supprimé avec succès.');
    } else {
        // Ajoutez un message Flash en cas de jeton CSRF invalide
        $this->addFlash('error', 'Échec de la suppression du tag. Jeton CSRF invalide.');
    }

    // Redirigez vers la liste des tags après la suppression
    return $this->redirectToRoute('tag_index');
}
}
