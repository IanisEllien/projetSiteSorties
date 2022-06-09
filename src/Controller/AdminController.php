<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\DesactivationType;
use App\Form\InscriptionCsvType;
use App\Form\ParticipantType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\DocBlock\Serializer;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/admin", name="admin_")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/inscription/fichier", name="register_file")
     */
    public function creerUserCSV(EntityManagerInterface $entityManager,
                                CampusRepository $campusRepository,
                            UserPasswordHasherInterface $userPasswordHasher,
                            ParticipantRepository $participantRepository,
                                 Request $request,
                            SluggerInterface $slugger
    ){
        $form = $this->createForm(InscriptionCsvType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('fichier')->getData();
            $fileName = $uploadedFile->getClientOriginalName();
            //dd($fileName);
            $root = $this->getParameter('kernel.project_dir');

            $destination =($root . '/public/uploads');

            $uploadedFile->move(
                $destination,
                $fileName
            );
            //dd($uploadedFile);
           //dd($fileName);

            //$originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            //$safeFilename = $slugger->slug($originalFilename);
            //$newFilename = $safeFilename. '-' . uniqid() . '.' . $uploadedFile->guessExtension();
            //dd($newFilename);

            $compteur = 0;

            //dd($root);
            $file = fopen($root.'/public/uploads/'.$fileName, 'r');
            while (($line = fgetcsv($file)) !== FALSE) {
            //while (($line = $data) != FALSE) {
                //dd($line);
                $participant = new Participant();
                $participant->setPseudo($line[0]);
                $participant->setPrenom($line[1]);
                $participant->setNom($line[2]);
                $participant->setTelephone($line[3]);
                $participant->setEmail($line[4]);
                $participant->setPassword(
                    $userPasswordHasher->hashPassword($participant,
                        $line[5]
                    )
                );
                $participant->setRoles(["ROLE_USER"]);
                $participant->setAdministrateur(false);
                $participant->setActif(true);
                $campus = $campusRepository->findOneBy(['nom' => $line[6]]);

                $participant->setCampus($campus);
                //dd($participant);
                $ancienParticipant = $participantRepository->findOneBy(['email' => $participant->getEmail(), 'pseudo' => $participant->getPseudo()]);
                if (!$ancienParticipant) {
                    $entityManager->persist($participant);
                    $entityManager->flush();

                    $compteur++;
                }

            }

            //dd($compteur);
            fclose($file);
            //dd($file);
            $this->addFlash('success', $compteur . ' utilisateur(s) ajouté(s) avec succés !');

        }


        return $this->render('registration/register-csv.html.twig', [
            'csvForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/inscription", name="register")
     */
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new Participant();
        $user->setRoles(["ROLE_USER"]);
        $user->setAdministrateur(false);
        $user->setActif(true);
        $form = $this->createForm(ParticipantType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success','Utilisateur ajouté avec succés !');

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/desactivation", name="desactiver")
     */
    public function desactiver(Request $request, ParticipantRepository $participantRepository, EntityManagerInterface $em): Response
    {
        $participants = $participantRepository->findBy(['actif' => 1], ['pseudo' => 'ASC']);

        $ids = $request->request->get('userID');
        $method = $request->getMethod();

        if ($method === 'POST') {
            if ($ids) {
                $cpt = 0;
                foreach ($ids as $id) {
                    dump($ids);
                    $participant = $participantRepository->find($id);
                    $participant->setActif(false);
                    $em->persist($participant);
                    $cpt++;
                }
                $em->flush();

                if ($cpt == 1) {
                    $this->addFlash('success', $cpt . ' participant a bien été désactivé');
                } else {
                    $this->addFlash('success', $cpt . ' participants ont bien été désactivés');
                }
                return $this->redirectToRoute('sorties_liste');
            } else {
                $this->addFlash('warning', 'Veuillez sélectionner au moins un participant');
            }
        }

        return $this->render('user/desactiver.html.twig', [
            "participants" => $participants
        ]);
    }

    /**
     * @Route("/suppression", name="supprimer")
     */
    public function supprimer(Request $request, ParticipantRepository $participantRepository, SortieRepository $sortieRepository, EntityManagerInterface $em): Response
    {
        $participants = $participantRepository->findBy([],['pseudo'=>'ASC']);


        $ids = $request->request->get('userID');
        $method = $request->getMethod();

        if ($method === 'POST') {
            if ($ids) {
                $cpt = 0;
                $sortieEffacee = false;

                foreach ($ids as $id) {

                    $participant = $participantRepository->find($id);
                    $sortie = $sortieRepository->findBy(['organisateur'=>$id]);
                    if ($sortie){
                        $sortieEffacee = true;
                    }
                    $participant->setActif(false);
                    $em->remove($participant);
                    $cpt++;
                }
                $em->flush();

                if ($cpt == 1) {
                    $this->addFlash('success', $sortieEffacee?$cpt . ' participant a bien été supprimé. Une ou plusieurs sorties associées ont également été supprimées':$cpt . ' participant a bien été supprimé');
                } else {
                    $this->addFlash('success', $sortieEffacee?$cpt . ' participants ont bien été supprimés. Une ou plusieurs sorties associées ont également été supprimées':$cpt . ' participants ont bien été supprimés');
                }
                return $this->redirectToRoute('sorties_liste');
            } else {
                $this->addFlash('warning', 'Veuillez sélectionner au moins un participant');
            }
        }

        return $this->render('user/supprimer.html.twig', [
            'participants'=> $participants
        ]);
    }


}
