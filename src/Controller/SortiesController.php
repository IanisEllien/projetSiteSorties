<?php

namespace App\Controller;

use App\Data\FiltreSortie;
use App\Entity\Sortie;
use App\Form\FiltreSortieType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Services\ServicesSorties;
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
    public function creer(Request $request,
                          EntityManagerInterface $entityManager,
                          EtatRepository $etatRepository,
                          LieuRepository $lieuRepository
    ): Response
    {
        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        $lieux = $lieuRepository->findAll();
        //dd($lieux);


        if($sortieForm->isSubmitted()){
            $lieu = $sortie->getLieu();
            $ancienLieu = $lieuRepository->findOneBy(['nom' => $lieu->getNom(), 'rue' => $lieu->getRue()]);

            if(!$ancienLieu){
                $entityManager->persist($lieu);
                $entityManager->flush();
                $this->addFlash('success','Lieu ajouté avec succés !');
            }


            if ($sortieForm->getClickedButton() === $sortieForm->get('enregistrerSortie') && $sortieForm->isValid() || $sortieForm->getClickedButton() === $sortieForm->get('publierSortie') && $sortieForm->isValid()){
                $ancienLieu = $lieuRepository->findOneBy(['nom' => $lieu->getNom(), 'rue' => $lieu->getRue()]);
                if($ancienLieu){
                    $sortie->setLieu($ancienLieu);
                }
                else{
                    $sortie->setLieu($lieu);
                    $entityManager->persist($lieu);
                    $entityManager->flush();
                    $this->addFlash('success','Lieu ajouté avec succés !');
                }

                if ($sortieForm->getClickedButton() === $sortieForm->get('enregistrerSortie')){
                    $etat = $etatRepository->findOneBy(['libelle' => 'Créée']);
                }
                else{
                    $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
                }


                $sortie->setCampus($this->getUser()->getCampus());
                $sortie->setEtat($etat);
                $sortie->setOrganisateur($this->getUser());
                //dd($sortie);
                //$entityManager->persist($lieu);
                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash('success','Sortie ajoutée avec succés !');
            }



        }

        return $this->render('sorties/createSortie.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            'lieux' => $lieux

        ]);
    }

    /**
     * @Route("/inscription/{id}", requirements={"id"="\d+"}, name="inscription")
     */
    public function inscription(EntityManagerInterface $em, SortieRepository $SortieRepo, ParticipantRepository $participantRepo, ServicesSorties $ss, $id): Response
    {
        $idUser = $this->getUser()->getId();
        $participant = $participantRepo->find($idUser);
        $sortie = $SortieRepo->find($id);
        $participants = $sortie->getParticipants();

        // Vérification si la sortie est en statut Ouverte, et si elle n'est pas clôturée
        if (!($sortie->getEtat()->getLibelle()==='Ouverte') || ($ss->estComplete($sortie))){
            $this->addFlash('danger','Les inscriptions ne sont pas ouvertes pour la sortie'.$sortie->getNom());
            return $this->redirectToRoute('sorties_liste');
        }

        // Vérification si le participant n'est pas déjà inscrit
        if ($ss->rechercheParticipant($participant, $participants)){
            $this->addFlash('warning','Vous êtes déjà inscrit à la sortie '.$sortie->getNom());
            return $this->redirectToRoute('sorties_liste');
        }

        // Vérification si le participant n'est pas l'organisateur
        if (!($ss->estOrganisateur($sortie, $participant))){
            $sortie->addParticipant($participant);
            $em->persist($sortie);
            $em->flush();

            $this->addFlash('success','Vous êtes bien inscrit à la sortie '.$sortie->getNom().' organisée par '.$sortie->getOrganisateur()->getPseudo());
            return $this->redirectToRoute('sorties_liste');
        }

        return $this->render('sorties/sortie.html.twig', [
            'controller_name' => 'SortiesController',
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
