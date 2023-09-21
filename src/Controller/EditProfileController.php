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

class EditProfileController extends AbstractController
{
    #[Route('/editPassword', name: 'app_edit_password')]
    public function index(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EditProfileFormType::class, $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $currentPassword = $form->get('currentPassword')->getData();
            $newPassword = $form->get('plainPassword')->get('first')->getData();

            if ($passwordHasher->isPasswordValid($user, $currentPassword)) {
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                $entityManager->flush();
                $this->addFlash('success', 'Votre mot de passe a été mis à jour');
            } else {
                $this->addFlash('error', 'Mot de passe actuel incorrect');
            }
        }

        return $this->render('edit_profile/editProfile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
