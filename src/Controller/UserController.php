<?php

namespace App\Controller;

use App\Form\RegistrationFormType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $form = $this->createForm(RegistrationFormType::class, $user);
        dump($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $em->persist($user);
            $em->flush();
            dump($user);
            // do anything else you need here, like send an email

            $this->addFlash('success','Vos modifications ont bien été enregistrées');
            return $this->redirectToRoute('sorties_liste');
        }

        return $this->render('user/profilUser.html.twig', [
            "modificationForm"=>$form->createView(),
            "currentUser"=>true
        ]);
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="afficher")
     */
    public function afficher(): Response
    {
        return $this->render('user/profilUser.html.twig', [
            "currentUser"=>false
        ]);
    }
}
