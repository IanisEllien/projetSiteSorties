<?php

namespace App\Controller;

use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profil", name="user_")
 */
class UserController extends AbstractController
{

    /**
     * @Route("/modifier", name="modifier")
     */
    public function modifier(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em, ParticipantRepository $repo): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ParticipantType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //Gestion de l'image uploadée
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['photo']->getData();

            if ($uploadedFile) {
                $destination = $this->getParameter('kernel.project_dir') . '/public/uploads';
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
                $uploadedFile->move(
                    $destination,
                    $newFilename
                );
                $user->setPhoto($newFilename);
            }

            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $em->persist($user);
            $em->flush();
            // do anything else you need here, like send an email

            $this->addFlash('success','Vos modifications ont bien été enregistrées');
            return $this->redirectToRoute('sorties_liste');
        }

        return $this->render('user/modifierProfil.html.twig', [
            "modificationForm"=>$form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="afficher")
     */
    public function afficher(int $id, ParticipantRepository $participantRepository): Response
    {
        $participant = $participantRepository->find($id);

        if(!$participant){
            throw $this->createNotFoundException('Ce profil n\'existe pas !');
        }

        return $this->render('user/afficherProfil.html.twig', [
            "participant" => $participant
        ]);
    }
}
