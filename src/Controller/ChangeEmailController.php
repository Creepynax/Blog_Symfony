<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangeEmailFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ChangeEmailController extends AbstractController
{
    #[Route('/changeEmail', name: 'app_change_email')]
    public function index(
        Request $request,
        TokenStorageInterface $tokenStorage,
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = $tokenStorage->getToken()->getUser();
        $form = $this->createForm(ChangeEmailFormType::class, $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $emailEmail = $form->get('newEmail')->getData();
            $user->setEmail($emailEmail);
            $entityManager->flush();
            $this->addFlash('success', 'Votre profil a été mis à jour');
        } else {
            $this->addFlash('error', 'Mot de passe actuel incorrect');
        }
        return $this->render('profile/changeEmail.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
