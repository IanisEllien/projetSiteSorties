<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_login")
     */
    public function login(): Response
    {
        return $this->render('user/login.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    /**
     * @Route("/monProfil", name="user_modifier")
     */
    public function modifier(): Response
    {
        return $this->render('user/profilUser.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    /**
     * @Route("/profil/{id}", requirements={"id"="\d+"}, name="user_afficher")
     */
    public function afficher(): Response
    {
        return $this->render('user/profilUser.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
}
