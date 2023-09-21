<?php

namespace App\Controller;

use App\Form\EditProfileFormType;
use Doctrine\ORM\EntityManagerInterface; // Import the EntityManagerInterface
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    private EntityManagerInterface $entityManager; // Declare the EntityManagerInterface

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager; // Inject the EntityManagerInterface
    }

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
                $this->entityManager->flush(); // Use the injected EntityManagerInterface
                $this->addFlash('success', 'Votre profil a été mis à jour');
                return $this->redirectToRoute('app_profile');
            }

            return $this->render('profile/profile.html.twig', [
                'user' => $user,
                'editMode' => true,
                'form' => $form->createView(),
            ]);
        }

        return $this->render('profile/profile.html.twig', [
            'user' => $user,
            'editMode' => false,
        ]);
    }
}
