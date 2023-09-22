<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\EditProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProfileController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[IsGranted('ROLE_USER', statusCode: 403)]
    #[Route('/profile', name: 'app_profile')]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        $editMode = $request->query->get('edit', false);

        if ($editMode) {
            $form = $this->createForm(EditProfileFormType::class, $user, [
                'action' => $this->generateUrl('app_edit_profile'),
                'method' => 'POST',
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->entityManager->flush();
                $this->addFlash('success', 'Votre profil a été mis à jour');
                return $this->redirectToRoute('app_profile');
            }

            return $this->render('profile/profile.html.twig', [
                'user' => $user,
                'editMode' => true,
                'form' => $form->createView(),
            ]);
        } else {
            // Récupérez les articles de l'utilisateur actuel
            $articles = $this->entityManager->getRepository(Article::class)->findBy(['user' => $user]);

            return $this->render('profile/profile.html.twig', [
                'user' => $user,
                'editMode' => false,
                'articles' => $articles, // Liste personnalisée des articles de l'utilisateur
            ]);
        }
    }

    #[Route('/profile/delete', name: 'app_delete_profile')]
    public function delete(
        Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage
    ): Response {
        $user = $this->getUser();
        $entityManager->remove($user);
        $entityManager->flush();
        $tokenStorage->setToken(null);
        $request->getSession()->invalidate();
        $this->addFlash('success', 'Votre profil a été supprimé');
        return $this->redirectToRoute('app_home');
    }
}
