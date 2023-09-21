<?php

// src/Controller/TagController.php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

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

    #[Route('/{id}', name: 'tag_show', methods: ['GET'])]
    public function show(Tag $tag): Response
    {
        return $this->render('tag/show.html.twig', [
            'tag' => $tag,
        ]);
    }

    #[Route('/new', name: 'tag_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $doctrine): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($tag);
            $entityManager->flush();

            return $this->redirectToRoute('tag_index');
        }

        return $this->render('tag/new.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    #[Route('{id}/edit', name: 'tag_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tag $tag, EntityManagerInterface $doctrine): Response
    {
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $doctrine->getManager()->flush();

            return $this->redirectToRoute('tag_index');
        }

        return $this->render('tag/edit.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'tag_delete', methods: ['DELETE'])]
    public function delete(Request $request, Tag $tag, EntityManagerInterface $doctrine): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tag->getId(), $request->request->get('_token'))) {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($tag);
            $entityManager->flush();
        }

        return $this->redirectToRoute('tag_index');
    }
}
