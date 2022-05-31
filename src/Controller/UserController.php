<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profil", name="user_")
 */
class UserController extends AbstractController
{

    /**
     * @Route("/modifier", name="modifier")
     */
    public function modifier(): Response
    {
        return $this->render('user/profilUser.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="afficher")
     */
    public function afficher(): Response
    {
        return $this->render('user/profilUser.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
}
