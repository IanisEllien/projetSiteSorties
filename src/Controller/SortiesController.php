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

        if (!$form->isSubmitted())
        {
            $filtres->typeSortie = ['orga','inscrit','noninscrit'];
        }

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

        if ($sortieForm->getClickedButton() === $sortieForm->get('enregistrerSortie') && $sortieForm->isValid() || $sortieForm->getClickedButton() === $sortieForm->get('publierSortie') && $sortieForm->isValid()){
            $lieu = $sortie->getLieu();
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
            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success','Sortie ajoutée avec succés !');
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
        $today = new \DateTime();

        // Vérification si la sortie est en statut Ouverte, et si elle n'est pas clôturée
        if (!($sortie->getEtat()->getLibelle()==='Ouverte') || ($ss->estComplete($sortie)) || $sortie->getDateLimiteInscription()<$today){
            $this->addFlash('danger','Les inscriptions ne sont pas ouvertes pour la sortie'.$sortie->getNom().' organisée par '.$sortie->getOrganisateur()->getPseudo());
            return $this->redirectToRoute('sorties_liste');
        }

        // Vérification si le participant n'est pas déjà inscrit
        if ($ss->rechercheParticipant($participant, $participants)){
            $this->addFlash('warning','Vous êtes déjà inscrit à la sortie '.$sortie->getNom().' organisée par '.$sortie->getOrganisateur()->getPseudo());
            return $this->redirectToRoute('sorties_liste');
        }

        // Vérification si le participant n'est pas l'organisateur
        if (!($ss->estOrganisateur($sortie, $participant))){
            $sortie->addParticipant($participant);
            $em->persist($sortie);
            $em->flush();

            $this->addFlash('success','Vous êtes bien inscrit à la sortie '.$sortie->getNom().' organisée par '.$sortie->getOrganisateur()->getPseudo());
        } else {
            $this->addFlash('warning','Vous ne pouvez pas vous inscrire à une sortie que vous organisez');
        }
        return $this->redirectToRoute('sorties_liste');

    }

    /**
     * @Route("/desistement/{id}", requirements={"id"="\d+"}, name="desistement")
     */
    public function desistement(EntityManagerInterface $em, SortieRepository $SortieRepo, ParticipantRepository $participantRepo, ServicesSorties $ss, $id): Response
    {
        $idUser = $this->getUser()->getId();
        $participant = $participantRepo->find($idUser);
        $sortie = $SortieRepo->find($id);
        $participants = $sortie->getParticipants();

        //Vérification si la sortie est en statut ouverte, et si le participant est bien inscrit
        if (!($sortie->getEtat()->getLibelle()==='Ouverte')){
            $this->addFlash('danger','Il n\'est plus possible de vous désister de la sortie '.$sortie->getNom().' organisée par '.$sortie->getOrganisateur()->getPseudo());
            return $this->redirectToRoute('sorties_liste');
        }

        //Vérification si le participant est bien inscrit
        if (!($ss->rechercheParticipant($participant, $participants))){
            $this->addFlash('danger','Impossible de vous désister car vous n\'êtes pas inscrit à la sortie  '.$sortie->getNom().' organisée par '.$sortie->getOrganisateur()->getPseudo());
            return $this->redirectToRoute('sorties_liste');
        }

        // Vérification si le participant n'est pas l'organisateur
        if ($ss->estOrganisateur($sortie, $participant)) {
            $this->addFlash('warning','Vous ne pouvez pas vous inscrire à une sortie que vous organisez');
            return $this->redirectToRoute('sorties_liste');
        }

        $sortie->removeParticipant($participant);
        $em->persist($sortie);
        $em->flush();

        $this->addFlash('success','Votre désistement pour la sortie '.$sortie->getNom().' organisée par '.$sortie->getOrganisateur()->getPseudo().' a bien été pris en compte');

        return $this->redirectToRoute('sorties_liste');
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="afficher")
     */
    public function afficher(SortieRepository $sortieRepo, $id): Response
    {
        $sortie = $sortieRepo->find($id);

        return $this->render('sorties/affichageSortie.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    /**
     * @Route("/publier/{id}", requirements={"id"="\d+"}, name="publier")
     */
    public function publier (EntityManagerInterface $em, SortieRepository $SortieRepo, ParticipantRepository $participantRepo, ServicesSorties $ss, EtatRepository $etatRepo, $id): Response
    {

        $sortie = $SortieRepo->find($id);
        $idUser = $this->getUser()->getId();
        $participant = $participantRepo->find($idUser);

        if ($ss->estOrganisateur($sortie, $participant)) {
            if ($sortie->getEtat()->getLibelle() == 'Créée') {
                $etat = $etatRepo->findOneBy(['libelle' => 'Ouverte']);
                $sortie->setEtat($etat);
                $em->persist($sortie);
                $em->flush();

                $this->addFlash('success', 'Votre sortie ' . $sortie->getNom() . ' est désormais publiée, et sera visible par les autres utilisateurs');

                return $this->redirectToRoute('sorties_liste');

            } else {
                $this->addFlash('warning', 'La sortie ' . $sortie->getNom() . ' a déjà été publiée');

                return $this->redirectToRoute('sorties_liste');
            }
        } else {
            $this->addFlash('danger', 'Vous ne pouvez pas publier la sortie ' . $sortie->getNom() . ' car vous n\'en êtes pas l\'organisateur !');

            return $this->redirectToRoute('sorties_liste');
        }
    }

    /**
     * @Route("/modifier/{id}", requirements={"id"="\d+"}, name="modifier")
     */
    public function modifier(int $id, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);

        return $this->render('sorties/sortieModifier.html.twig', [
            'controller_name' => 'SortiesController',
            'sortie' => $sortie
        ]);
    }

    /**
     * @Route("/annuler/{id}", requirements={"id"="\d+"}, name="annuler")
     */
    public function annuler(int $id, SortieRepository $sortieRepository): Response
    {
         $sortie = $sortieRepository->find($id);

        if(!$sortie)
        {
            throw $this->createNotFoundException('La sortie que vous cherchez à annuler n\'existe pas');
        }

        /*
        if()
        {

        }
        */

        return $this->render('sorties/annulerSortie.html.twig', [
            'controller_name' => 'SortiesController',
            'sortie' => $sortie
        ]);
    }
}
