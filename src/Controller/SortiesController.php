<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortiesController extends AbstractController
{
    /**
     * @Route("/accueil", name="sorties_liste")
     */
    public function liste(): Response
    {
        return $this->render('sorties/listeSorties.html.twig', [
            'controller_name' => 'SortiesController',
        ]);
    }

    /**
     * @Route("/creationSortie", name="sorties_creer")
     */
    public function creer(): Response
    {
        return $this->render('sorties/createSortie.html.twig', [
            'controller_name' => 'SortiesController',
        ]);
    }

    /**
     * @Route("/sortie/{id}", requirements={"id"="\d+"}, name="sorties_afficher")
     */
    public function afficher(): Response
    {
        return $this->render('sorties/sortie.html.twig', [
            'controller_name' => 'SortiesController',
        ]);
    }

    /**
     * @Route("/sortie/{id}", requirements={"id"="\d+"}, name="sorties_modifier")
     */
    public function modifier(): Response
    {
        return $this->render('sorties/sortie.html.twig', [
            'controller_name' => 'SortiesController',
        ]);
    }

    /**
     * @Route("/sortie/{id}", requirements={"id"="\d+"}, name="sorties_annuler")
     */
    public function annuler(): Response
    {
        return $this->render('sorties/sortie.html.twig', [
            'controller_name' => 'SortiesController',
        ]);
    }
}
