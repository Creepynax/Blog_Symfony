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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Form\ChangeEmailFormType;

class EditProfileController extends AbstractController
{
    #[Route('/editProfile', name: 'app_edit_profile')]
    public function index(
        Request $request,
        TokenStorageInterface $tokenStorage,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $tokenStorage->getToken()->getUser();
        $emailForm = $this->createForm(ChangeEmailFormType::class);
        $emailForm->handleRequest($request);
        $passwordForm = $this->createForm(EditProfileFormType::class, $this->getUser());
        $passwordForm->handleRequest($request);

        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            $newEmail = $emailForm->get('newEmail')->getData();

            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $newEmail]);
            if (!$existingUser) {
                $user->setEmail($newEmail);
                $entityManager->flush();
                $this->addFlash('success', 'Your email address has been updated');
            } else {
                $this->addFlash('error', 'This email address is already in use');
            }
        }

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            /** @var User $user */
            $user = $passwordForm->getData();
            $currentPassword = $passwordForm->get('currentPassword')->getData();
            $newPassword = $passwordForm->get('plainPassword')->get('first')->getData();

            if ($passwordHasher->isPasswordValid($user, $currentPassword)) {
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                $entityManager->flush();
                $this->addFlash('success', 'Votre profil a été mis à jour');
            } else {
                $this->addFlash('error', 'Mot de passe actuel incorrect');
            }
        }

        return $this->render('edit_profile/editProfile.html.twig', [
            'emailForm' => $emailForm->createView(),
            'passwordForm' => $passwordForm->createView(),
        ]);
    }
}
