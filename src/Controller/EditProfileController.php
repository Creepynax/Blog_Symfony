<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditProfileFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EditProfileController extends AbstractController
{
    #[Route('/editProfile', name: 'app_edit_profile')]
    public function index(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(EditProfileFormType::class, $this->getUser());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            $plainPassword = $form->get('plainPassword')->getData();
            if (null !== $plainPassword) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }
            $entityManager->flush();
            $this->addFlash('success', 'Votre profil a été mis à jour');
        }
        return $this->render('edit_profile/editProfile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
