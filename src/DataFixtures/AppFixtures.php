<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {

        // Campus

        $campus = new Campus();
        $campus->setNom('SAINT-HERBLAIN');
        $manager->persist($campus);

        $campus = new Campus();
        $campus->setNom('CHARTRES DE BRETAGNE');
        $manager->persist($campus);

        $campus = new Campus();
        $campus->setNom('LA ROCHE SUR YON');
        $manager->persist($campus);

        // Participants

        $participant = new Participant();
        $participant->setPseudo('JDoe');
        $participant->setNom('Doe');
        $participant->setPrenom('John');
        $participant->setTelephone('0621524210');
        $participant->setEmail('john@campus-eni.fr');
        $password = $this->hasher->hashPassword($participant,'Pa$$w0rd');
        $participant->setPassword($password);
        $participant->setAdministrateur(false);
        $participant->setRoles(["ROLE_USER"]);
        $participant->setActif(true);
        $participant->setCampus($campus);
        $manager->persist($participant);

        $participant = new Participant();
        $participant->setPseudo('ADMIN');
        $participant->setNom('Min');
        $participant->setPrenom('AD');
        $participant->setTelephone('0754896234');
        $participant->setEmail('administrateur@campus-eni.fr');
        $password = $this->hasher->hashPassword($participant,'Pa$$w0rd');
        $participant->setPassword($password);
        $participant->setAdministrateur(true);
        $participant->setRoles(["ROLE_ADMIN"]);
        $participant->setActif(true);
        $participant->setCampus($campus);
        $manager->persist($participant);

        // Etats

        $etat = new Etat();
        $etat->setLibelle('Créée');
        $manager->persist($etat);

        $etat = new Etat();
        $etat->setLibelle('Clôturée');
        $manager->persist($etat);

        $etat = new Etat();
        $etat->setLibelle('Activité en cours');
        $manager->persist($etat);

        $etat = new Etat();
        $etat->setLibelle('Passée');
        $manager->persist($etat);

        $etat = new Etat();
        $etat->setLibelle('Annulée');
        $manager->persist($etat);

        $etat = new Etat();
        $etat->setLibelle('Ouverte');
        $manager->persist($etat);

        // Sorties

        $sortie = new Sortie();
        $sortie->setNom('Visite au musée');
        $sortie->setDateHeureDebut(new \DateTime('2022-06-15 15:00:00'));
        $sortie->setDateLimiteInscription(new \DateTime('2022-06-08 17:00:00'));
        $sortie->setDuree(90);
        $sortie->setNbInscriptionMax(15);
        $sortie->setCampus($campus);
        $sortie->setEtat($etat);
        $sortie->setOrganisateur($participant);
        $sortie->setInfosSortie('Nous irons au musée Paul Gaugin afin d\'apprécier les oeuvres d\'art de ce grand peintre.');
        $manager->persist($sortie);

        $sortie = new Sortie();
        $sortie->setNom('Restaurant pizzeria');
        $sortie->setDateHeureDebut(new \DateTime('2022-06-21 20:00:00'));
        $sortie->setDateLimiteInscription(new \DateTime('2022-06-14 17:00:00'));
        $sortie->setDuree(120);
        $sortie->setNbInscriptionMax(10);
        $sortie->setCampus($campus);
        $sortie->setEtat($etat);
        $sortie->setOrganisateur($participant);
        $sortie->setInfosSortie('Profitons de ce solstice d\'été afin de se réunir autour d\'une bonne pizza chez Giovanni');
        $manager->persist($sortie);

        $manager->flush();
    }
}
