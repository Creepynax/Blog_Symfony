<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EditProfileController extends AbstractController
{
    #[Route('/edit/profile', name: 'app_edit_profile')]
    public function index(): Response
    {
        return $this->render('edit_profile/index.html.twig', [
            'controller_name' => 'EditProfileController',
        ]);
    }
}
