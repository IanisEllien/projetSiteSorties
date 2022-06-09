<?php

namespace App\Controller;

use App\Data\FiltreSortie;
use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\FiltreSortieType;
use App\Form\LieuType;
use App\Form\SortieType;
use App\Form\VilleType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use App\Services\ServicesSorties;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
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
    public function liste(SortieRepository $repository, Request $request, CampusRepository $campusRepository): Response
    {

        //On instancie une date à N-1 mois + user.pseudo
        $date = new \DateTime('now-1month');
        $user = $this->getUser();

        if (!empty($request->query->all()))
        {
            $filtres = $request->query->all();
            $sorties = $repository->filtreListeSorties($user, $filtres, $date);
        }
        else
        {
            $sorties = $repository->listeSortiesMoinsUnMois($date);
        }

        //dump($filtres);

        $campus = $campusRepository->findAll();

        return $this->render('sorties/listeSorties.html.twig', [
            'sorties' => $sorties,
            'campus' => $campus
        ]);
    }

    /**
     * @Route("/creation/ville", name="creer_ville")
     */
    public function creerVille(Request $request,
                               EntityManagerInterface $entityManager,
                                VilleRepository $villeRepository
    ) : Response {

        $ville = new Ville();
        $villeForm = $this->createForm(VilleType::class, $ville);
        $villeForm->handleRequest($request);

        if($villeForm->isSubmitted() && $villeForm->isValid()){

            $ancienneVille = $villeRepository->findOneBy(['nom' => $ville->getNom()]);
            if($ancienneVille){
                $this->addFlash('warning','Cette ville existait déjà !');
                $this->redirectToRoute('sorties_creer');
            }
            else{
                $entityManager->persist($ville);
                $entityManager->flush();
                $this->addFlash('success','Ville ajoutée avec succés !');
                $this->redirectToRoute('sorties_creer');
            }

            return $this->redirectToRoute('sorties_creer', [

            ]);

        }

        return $this->render('sorties/createVille.html.twig', [
            'villeForm' => $villeForm->createView(),

        ]);
    }

    /**
     * @Route("/creation/lieu", name="creer_lieu")
     */
    public function creerLieu(Request $request,
                              EntityManagerInterface $entityManager,
                              LieuRepository $lieuRepository

    ): Response {

        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);

        if($lieuForm->isSubmitted() && $lieuForm->isValid()){

            $ancienLieu = $lieuRepository->findOneBy(['nom' => $lieu->getNom(), 'rue' => $lieu->getRue()]);
            if($ancienLieu){
                $this->addFlash('warning','Ce lieu existait déjà !');
                $this->redirectToRoute('sorties_creer');
            }
            else{
                $entityManager->persist($lieu);
                $entityManager->flush();
                $this->addFlash('success','Lieu ajouté avec succés !');
                $this->redirectToRoute('sorties_creer');
            }

            return $this->redirectToRoute('sorties_creer', [

            ]);

        }

        return $this->render('sorties/createLieu.html.twig', [
            'lieuForm' => $lieuForm->createView(),

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

            $dateSortie = $sortie->getDateHeureDebut();
            $dateLimiteInscription = $sortie->getDateLimiteInscription();
            if($dateLimiteInscription > $dateSortie){
                $this->addFlash('warning','La date limite d\'inscription doit être antérieure à la date de sortie');
                $this->redirectToRoute('sorties_creer');
                //throw new Exception("La date limite d'inscription doit être inférieure à la date de sortie");
            }

            else{
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

                return $this->redirectToRoute('sorties_liste', [
                    'controller_name' => 'SortiesController',
                ]);
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
    public function annuler(int $id, SortieRepository $sortieRepository, Request  $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        $idUser = $this->getUser()->getId();
        $userRole = $this->getUser()->getRoles();
        //dd($userRole);
        $sortie = $sortieRepository->find($id);
        $idUserSortie = $sortie->getOrganisateur()->getId();
        $etatSortie = $sortie->getEtat()->getLibelle();
        //dd($etatSortie);
        $description = $sortie->getInfosSortie();
        $dateSortie = $sortie->getDateHeureDebut();
        $dateCloture = $sortie->getDateLimiteInscription();

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if(!$sortie)
        {
            throw $this->createNotFoundException('La sortie que vous cherchez à annuler n\'existe pas');
        }

        if($etatSortie == "Ouverte" or $etatSortie == "Créée"){

            if(($idUser === $idUserSortie) or $userRole[0] == "ROLE_ADMIN"){

                if($sortieForm->isSubmitted() && $sortieForm->isValid())
                {


                    $motifAnnulation = $sortie->getInfosSortie();

                    if($motifAnnulation == "")
                    {
                        $motifAnnulation = 'Pas de motif';
                    }

                    $sortie->setInfosSortie($description . "\r\n ANNULÉE CAR : " . $motifAnnulation);
                    $etat = $etatRepository->findOneBy(['libelle' => 'Annulée']);
                    $sortie->setEtat($etat);

                    // On remet les dates parce que sinon elles changent
                    $sortie->setDateHeureDebut($dateSortie);
                    $sortie->setDateLimiteInscription($dateCloture);

                    $entityManager->persist($sortie);
                    $entityManager->flush();

                    $this->addFlash('success','La sortie a bien été annulée');

                    return $this->redirectToRoute('sorties_liste', [
                        'controller_name' => 'SortiesController',
                    ]);
                }

            }

            else{
                $this->addFlash('warning','Vous n\'êtes pas autorisé à annuler cette sortie.');
            }

        }


        return $this->render('sorties/annulerSortie.html.twig', [
            'controller_name' => 'SortiesController',
            'sortie' => $sortie,
            'form' => $sortieForm->createView()
        ]);
    }
}
