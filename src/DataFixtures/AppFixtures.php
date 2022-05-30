<?php

namespace App\DataFixtures;

use App\Entity\Campus;
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
        $participant->setPseudo('j.delas');
        $participant->setNom('Delas');
        $participant->setPrenom('Jean');
        $participant->setTelephone('0621542310');
        $participant->setEmail('jean.delas@yahoo.fr');
        $password = $this->hasher->hashPassword($participant,'Pa$$w0rd');
        $participant->setPassword($password);
        $participant->setAdministrateur(false);
        $participant->setActif(true);
        $participant->setCampus($campus);
        $manager->persist($participant);

        $participant = new Participant();
        $participant->setPseudo('o.moulin');
        $participant->setNom('Moulin');
        $participant->setPrenom('Odile');
        $participant->setTelephone('0721389212');
        $participant->setEmail('odile.moulin@gmail.com');
        $password = $this->hasher->hashPassword($participant,'Azerty123!');
        $participant->setPassword($password);
        $participant->setAdministrateur(false);
        $participant->setActif(true);
        $participant->setCampus($campus);
        $manager->persist($participant);

        $participant = new Participant();
        $participant->setPseudo('admin');
        $participant->setNom('Admin');
        $participant->setPrenom('Admin');
        $participant->setTelephone(null);
        $participant->setEmail('admin@campus-eni.fr');
        $password = $this->hasher->hashPassword($participant,'admin0');
        $participant->setPassword($password);
        $participant->setAdministrateur(true);
        $participant->setActif(false);
        $participant->setCampus($campus);
        $manager->persist($participant);

        // Sorties

        $sortie = new Sortie();
        $sortie->setNom('Visite au musée');
        $sortie->setDateHeureDebut(new \DateTime('2022-06-15 15:00:00'));
        $sortie->setDateLimiteInscription(new \DateTime('2022-06-08 17:00:00'));
        $sortie->setDuree(90);
        $sortie->setNbInscriptionMax(15);
        $sortie->setCampus($campus);
        $sortie->setInfosSortie('Nous irons au musée Paul Gaugin afin d\'apprécier les oeuvres d\'art de ce grand peintre.');
        $manager->persist($sortie);

        $sortie = new Sortie();
        $sortie->setNom('Restaurant pizzeria');
        $sortie->setDateHeureDebut(new \DateTime('2022-06-21 20:00:00'));
        $sortie->setDateLimiteInscription(new \DateTime('2022-06-14 17:00:00'));
        $sortie->setDuree(120);
        $sortie->setNbInscriptionMax(10);
        $sortie->setCampus($campus);
        $sortie->setInfosSortie('Profitons de ce solstice d\'été afin de se réunir autour d\'une bonne pizza chez Giovanni');
        $manager->persist($sortie);

        $manager->flush();
    }
}
