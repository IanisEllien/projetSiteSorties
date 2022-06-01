<?php

namespace App\Controller;

use App\Data\FiltreSortie;
use App\Entity\Sortie;
use App\Form\FiltreSortieType;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sortie", name="sorties_")
 */
class SortiesController extends AbstractController
{
    /**
     * @Route("/accueil", name="liste")
     */
    public function liste(SortieRepository $repository, Request $request): Response
    {
        $filtres = new FiltreSortie();
        $form = $this->createForm(FiltreSortieType::class, $filtres);

        $form->handleRequest($request);

        //On instancie une date à N-1 mois + user.pseudo
        $date = new \DateTime('now-1month');
        $user = $this->getUser();

        $sorties = $repository->findAvecFiltres($filtres, $date, $user);


        return $this->render('sorties/listeSorties.html.twig', [
            'sorties' => $sorties,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/creation", name="creer")
     */
    public function creer(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        if($sortieForm->isSubmitted() && $sortieForm->isValid()){
            $entityManager->persist($sortie);
            $entityManager->flush();
        }

        return $this->render('sorties/createSortie.html.twig', [
            'sortieForm' => $sortieForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="afficher")
     */
    public function afficher(): Response
    {
        return $this->render('sorties/sortie.html.twig', [
            'controller_name' => 'SortiesController',
        ]);
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="modifier")
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
