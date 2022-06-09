<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker;


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

        // Pour test, participant inactif
        $participant = new Participant();
        $participant->setPseudo('Inactif');
        $participant->setNom('IN');
        $participant->setPrenom('Actif');
        $participant->setTelephone('0621524210');
        $participant->setEmail('inactif@campus-eni.fr');
        $password = $this->hasher->hashPassword($participant,'Pa$$w0rd');
        $participant->setPassword($password);
        $participant->setAdministrateur(false);
        $participant->setRoles(["ROLE_USER"]);
        $participant->setActif(false);
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

        /*$etat = new Etat();
        $etat->setLibelle('Passée');
        $manager->persist($etat);*/

        $etat = new Etat();
        $etat->setLibelle('Annulée');
        $manager->persist($etat);

        $etat = new Etat();
        $etat->setLibelle('Ouverte');
        $manager->persist($etat);

        // Villes

        $faker = Faker\Factory::create('fr_FR');

        // Ville qui sera utilisée pour tous les lieux
        $fakerPostCode = Faker\Provider\Address::postcode();
        $ville = new Ville();
        $ville->setNom($faker->city);
        $ville->setCodePostal($fakerPostCode);
        $manager->persist($ville);

        $villes = Array();
            for ($i = 0; $i<10; $i++) {
                $fakerPostCode = Faker\Provider\Address::postcode();
                $villes[$i] = new Ville();
                $villes[$i]->setNom($faker->city);
                $villes[$i]->setCodePostal($fakerPostCode);
                $manager->persist($villes[$i]);
            }

        // Lieux
        // Création de 20 participants, lieux et sorties via Faker

        $faker = Faker\Factory::create('fr_FR');
            $participants = Array();
            $lieux = Array();
            $sorties = Array();
            $etats = Array();
            $today = new \DateTime();
            for ($i = 0; $i<20; $i++){
               /* $lieux[$i] = new Lieu();
                $lieux[$i]->setNom($faker->words(2, true));
                $lieux[$i]->setRue($faker->streetAddress);
                $lieux[$i]->setLatitude($faker->latitude);
                $lieux[$i]->setLongitude($faker->longitude);
                $lieux[$i]->setVille($ville);

                $manager->persist( $lieux[$i]);*/

                $participants[$i] = new Participant();
                $participants[$i]->setPseudo($faker->userName);
                $participants[$i]->setNom($faker->lastName);
                $participants[$i]->setPrenom($faker->firstName);
                $participants[$i]->setTelephone($faker->phoneNumber);
                $participants[$i]->setEmail($faker->freeEmail);
                $password = $this->hasher->hashPassword($participants[$i],'Pa$$w0rd');
                $participants[$i]->setPassword($password);
                $participants[$i]->setAdministrateur(true);
                $participants[$i]->setRoles(["ROLE_USER"]);
                $participants[$i]->setActif(true);
                $participants[$i]->setCampus($campus);
                $manager->persist($participants[$i]);

                /*$sorties[$i] = new Sortie();
                $sorties[$i]->setNom($faker->sentence());
                $dateDebut = $faker->dateTimeBetween('- 3 months', '+ 1 month');
                $sorties[$i]->setDateHeureDebut($dateDebut);
                $sorties[$i]->setDateLimiteInscription($faker->dateTimeInInterval($dateDebut,'-1 week'));
                $sorties[$i]->setDuree($faker->randomNumber(3,false));
                $sorties[$i]->setNbInscriptionMax($faker->randomNumber(2,false));
                $sorties[$i]->setCampus($campus);
                    if ($dateDebut < $today){
                        $etats[$i] = new Etat();
                        $etats[$i]->setLibelle('Passée');
                        $manager->persist($etats[$i]);
                        $sorties[$i]->setEtat($etats[$i]);
                    } elseif ($dateDebut->format('d/m/y') === $today->format('d/m/y')){
                        $etats[$i] = new Etat();
                        $etats[$i]->setLibelle('Activité en cours');
                        $manager->persist($etats[$i]);
                        $sorties[$i]->setEtat($etats[$i]);
                    } else {
                        $etats[$i] = new Etat();
                        $etats[$i]->setLibelle('Ouverte');
                        $manager->persist($etats[$i]);
                        $sorties[$i]->setEtat($etats[$i]);
                    }
                $sorties[$i]->setOrganisateur($participants[$i]);
                $sorties[$i]->setLieu($lieux[$i]);
                $sorties[$i]->setInfosSortie($faker->text());
                $manager->persist($sorties[$i]);*/
            }

        // 5 Sorties organisées par ADMIN
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

       /////////////////////////// 1 ////////////////////////////////////////
        $fakerPostCode = Faker\Provider\Address::postcode();
        $ville = new Ville();
        $ville->setNom($faker->city);
        $ville->setCodePostal($fakerPostCode);
        $manager->persist($ville);

        $lieu = new Lieu();
        $lieu->setNom('Pizzeria Da Enzo');
        $lieu->setRue('80 rue Saint Charles');
        $lieu->setLatitude(48.8473024);
        $lieu->setLongitude(2.2859873);
        $lieu->setVille($ville);
        $manager->persist($lieu);

        $sortie = new Sortie();
        $sortie->setNom('Restaurant pizzeria');
        $sortie->setDateHeureDebut(new \DateTime('2022-06-21 20:00:00'));
        $sortie->setDateLimiteInscription(new \DateTime('2022-06-14 17:00:00'));
        $sortie->setDuree(120);
        $sortie->setNbInscriptionMax(0);
        $sortie->setCampus($campus);
        $sortie->setEtat($etat);
        $sortie->setOrganisateur($participant);
        $sortie->setLieu($lieu);
        $sortie->setInfosSortie('Profitons de ce solstice d\'été afin de se réunir autour d\'une bonne pizza chez Giovanni');
        $manager->persist($sortie);

        /////////////////////////// 2 ////////////////////////////////////////
        $fakerPostCode = Faker\Provider\Address::postcode();
        $ville = new Ville();
        $ville->setNom($faker->city);
        $ville->setCodePostal($fakerPostCode);
        $manager->persist($ville);

        $lieu = new Lieu();
        $lieu->setNom('Musée Paul Gaugin');
        $lieu->setRue($faker->streetAddress);
        $lieu->setLatitude($faker->latitude);
        $lieu->setLongitude($faker->longitude);
        $lieu->setVille($ville);
        $manager->persist($lieu);

        $sortie = new Sortie();
        $sortie->setNom('Visite au musée');
        $dateDebut = $faker->dateTimeBetween('- 3 months', '+ 1 month');
        $sortie->setDateHeureDebut($dateDebut);
        $sortie->setDateLimiteInscription($faker->dateTimeInInterval($dateDebut,'-1 week'));
        $sortie->setDuree($faker->randomNumber(3,false));
        $sortie->setNbInscriptionMax($faker->randomNumber(2,false));
        $sortie->setCampus($campus);
            if ($dateDebut < $today){
                $etat = new Etat();
                $etat->setLibelle('Passée');
                $manager->persist($etat);
                $sortie->setEtat($etat);
            } elseif ($dateDebut->format('d/m/y') === $today->format('d/m/y')){
                $etat = new Etat();
                $etat->setLibelle('Activité en cours');
                $manager->persist($etat);
                $sortie->setEtat($etat);
            } else {
                $etat = new Etat();
                $etat->setLibelle('Ouverte');
                $manager->persist($etat);
                $sortie->setEtat($etat);
                    }
                $sortie->setOrganisateur($participant);
                $sortie->setLieu($lieu);
                $sortie->setInfosSortie('Nous irons au musée Paul Gaugin afin d\'apprécier les oeuvres d\'art de ce grand peintre.');
                $manager->persist($sortie);

        /////////////////////////// 3 ////////////////////////////////////////

        $fakerPostCode = Faker\Provider\Address::postcode();
        $ville = new Ville();
        $ville->setNom($faker->city);
        $ville->setCodePostal($fakerPostCode);
        $manager->persist($ville);

        $lieu = new Lieu();
        $lieu->setNom('Complexe sportif municipal');
        $lieu->setRue($faker->streetAddress);
        $lieu->setLatitude($faker->latitude);
        $lieu->setLongitude($faker->longitude);
        $lieu->setVille($ville);
        $manager->persist($lieu);

        $sortie = new Sortie();
        $sortie->setNom('Tournoi de Badminton');
        $sortie->setDateHeureDebut(new \DateTime('2022-06-21 20:00:00'));
        $sortie->setDateLimiteInscription(new \DateTime('2022-06-01 17:00:00'));
        $sortie->setDuree(120);
        $sortie->setNbInscriptionMax(24);
        $sortie->setCampus($campus);
        $etat = new Etat();
        $etat->setLibelle('Ouverte');
        $manager->persist($etat);
        $sortie->setEtat($etat);
        $sortie->setOrganisateur($participant);
        $sortie->setLieu($lieu);
        $sortie->setInfosSortie('Tournoi de Badminton organisé par le BDE avec plusieurs lots à remporter');
        $manager->persist($sortie);

        /////////////////////////// 4 ////////////////////////////////////////

        $fakerPostCode = Faker\Provider\Address::postcode();
        $ville = new Ville();
        $ville->setNom($faker->city);
        $ville->setCodePostal($fakerPostCode);
        $manager->persist($ville);

        $lieu = new Lieu();
        $lieu->setNom('EX!T - Complexe Escape game');
        $lieu->setRue($faker->streetAddress);
        $lieu->setLatitude($faker->latitude);
        $lieu->setLongitude($faker->longitude);
        $lieu->setVille($ville);
        $manager->persist($lieu);

        $sortie = new Sortie();
        $sortie->setNom('Escape Game : à la recherche du bug dans cette superbe application');
        $sortie->setDateHeureDebut(new \DateTime('2022-06-12 20:00:00'));
        $sortie->setDateLimiteInscription(new \DateTime('2022-06-09 17:00:00'));
        $sortie->setDuree(120);
        $sortie->setNbInscriptionMax(6);
        $sortie->setCampus($campus);
        $etat = new Etat();
        $etat->setLibelle('Ouverte');
        $manager->persist($etat);
        $sortie->setEtat($etat);
        $sortie->setOrganisateur($participant);
        $sortie->setLieu($lieu);
        $sortie->setInfosSortie('6 esprits brillants seront au moins nécessaires pour réaliser cette tâche ardue');
        $manager->persist($sortie);

        /////////////////////////// 5 ////////////////////////////////////////

        $fakerPostCode = Faker\Provider\Address::postcode();
        $ville = new Ville();
        $ville->setNom($faker->city);
        $ville->setCodePostal($fakerPostCode);
        $manager->persist($ville);

        $lieu = new Lieu();
        $lieu->setNom('Cinéma du boulevard');
        $lieu->setRue($faker->streetAddress);
        $lieu->setLatitude($faker->latitude);
        $lieu->setLongitude($faker->longitude);
        $lieu->setVille($ville);
        $manager->persist($lieu);

        $sortie = new Sortie();
        $sortie->setNom('Marathon Star-Wars');
        $dateDebut = $faker->dateTimeBetween('- 3 months', '+ 1 month');
        $sortie->setDateHeureDebut($dateDebut);
        $sortie->setDateLimiteInscription($faker->dateTimeInInterval($dateDebut,'-1 week'));
        $sortie->setDuree($faker->randomNumber(3,false));
        $sortie->setNbInscriptionMax($faker->randomNumber(2,false));
        $sortie->setCampus($campus);
        if ($dateDebut < $today){
            $etat = new Etat();
            $etat->setLibelle('Passée');
            $manager->persist($etat);
            $sortie->setEtat($etat);
        } elseif ($dateDebut->format('d/m/y') === $today->format('d/m/y')){
            $etat = new Etat();
            $etat->setLibelle('Activité en cours');
            $manager->persist($etat);
            $sortie->setEtat($etat);
        } else {
            $etat = new Etat();
            $etat->setLibelle('Ouverte');
            $manager->persist($etat);
            $sortie->setEtat($etat);
        }
        $sortie->setOrganisateur($participant);
        $sortie->setLieu($lieu);
        $sortie->setInfosSortie('Diffusion en continue des 9 épisodes de la saga principale. Prévoyez de ne pas dormir.');
        $manager->persist($sortie);

        // 5 Sorties organisées par des utilisateurs standards générés aléatoirement

        /////////////////////////// 1 ////////////////////////////////////////
        $participant = new Participant();
        $participant->setPseudo($faker->userName);
        $participant->setNom($faker->lastName);
        $participant->setPrenom($faker->firstName);
        $participant->setTelephone($faker->phoneNumber);
        $participant->setEmail($faker->freeEmail);
        $password = $this->hasher->hashPassword($participant,'Pa$$w0rd');
        $participant->setPassword($password);
        $participant->setAdministrateur(false);
        $participant->setRoles(["ROLE_USER"]);
        $participant->setActif(true);
        $participant->setCampus($campus);
        $manager->persist($participant);

        $fakerPostCode = Faker\Provider\Address::postcode();
        $ville = new Ville();
        $ville->setNom($faker->city);
        $ville->setCodePostal($fakerPostCode);
        $manager->persist($ville);

        $lieu = new Lieu();
        $lieu->setNom('Complexe funTime');
        $lieu->setRue($faker->streetAddress);
        $lieu->setLatitude($faker->latitude);
        $lieu->setLongitude($faker->longitude);
        $lieu->setVille($ville);
        $manager->persist($lieu);

        $sortie = new Sortie();
        $sortie->setNom('Bowling');
        $dateDebut = $faker->dateTimeBetween('- 3 months', '+ 1 month');
        $sortie->setDateHeureDebut($dateDebut);
        $sortie->setDateLimiteInscription($faker->dateTimeInInterval($dateDebut,'-1 week'));
        $sortie->setDuree($faker->randomNumber(3,false));
        $sortie->setNbInscriptionMax($faker->randomNumber(2,false));
        $sortie->setCampus($campus);
        if ($dateDebut < $today){
            $etat = new Etat();
            $etat->setLibelle('Passée');
            $manager->persist($etat);
            $sortie->setEtat($etat);
        } elseif ($dateDebut->format('d/m/y') === $today->format('d/m/y')){
            $etat = new Etat();
            $etat->setLibelle('Activité en cours');
            $manager->persist($etat);
            $sortie->setEtat($etat);
        } else {
            $etat = new Etat();
            $etat->setLibelle('Ouverte');
            $manager->persist($etat);
            $sortie->setEtat($etat);
        }
        $sortie->setOrganisateur($participant);
        $sortie->setLieu($lieu);
        $sortie->setInfosSortie('Petite sortie au bowling pour décompresser et s\'amuser sans pression');
        $manager->persist($sortie);

        /////////////////////////// 2 ////////////////////////////////////////
        $participant = new Participant();
        $participant->setPseudo($faker->userName);
        $participant->setNom($faker->lastName);
        $participant->setPrenom($faker->firstName);
        $participant->setTelephone($faker->phoneNumber);
        $participant->setEmail($faker->freeEmail);
        $password = $this->hasher->hashPassword($participant,'Pa$$w0rd');
        $participant->setPassword($password);
        $participant->setAdministrateur(false);
        $participant->setRoles(["ROLE_USER"]);
        $participant->setActif(true);
        $participant->setCampus($campus);
        $manager->persist($participant);

        $fakerPostCode = Faker\Provider\Address::postcode();
        $ville = new Ville();
        $ville->setNom($faker->city);
        $ville->setCodePostal($fakerPostCode);
        $manager->persist($ville);

        $lieu = new Lieu();
        $lieu->setNom('lac du bonson');
        $lieu->setRue($faker->streetAddress);
        $lieu->setLatitude($faker->latitude);
        $lieu->setLongitude($faker->longitude);
        $lieu->setVille($ville);
        $manager->persist($lieu);

        $sortie = new Sortie();
        $sortie->setNom('Festival du lac');
        $dateDebut = $faker->dateTimeBetween('- 3 months', '+ 1 month');
        $sortie->setDateHeureDebut($dateDebut);
        $sortie->setDateLimiteInscription($faker->dateTimeInInterval($dateDebut,'-1 week'));
        $sortie->setDuree($faker->randomNumber(3,false));
        $sortie->setNbInscriptionMax($faker->randomNumber(2,false));
        $sortie->setCampus($campus);
        if ($dateDebut < $today){
            $etat = new Etat();
            $etat->setLibelle('Passée');
            $manager->persist($etat);
            $sortie->setEtat($etat);
        } elseif ($dateDebut->format('d/m/y') === $today->format('d/m/y')){
            $etat = new Etat();
            $etat->setLibelle('Activité en cours');
            $manager->persist($etat);
            $sortie->setEtat($etat);
        } else {
            $etat = new Etat();
            $etat->setLibelle('Ouverte');
            $manager->persist($etat);
            $sortie->setEtat($etat);
        }
        $sortie->setOrganisateur($participant);
        $sortie->setLieu($lieu);
        $sortie->setInfosSortie('Allons fêter la fin des examens en écoutant de la bonne musique !');
        $manager->persist($sortie);

        /////////////////////////// 3 ////////////////////////////////////////
        $participant = new Participant();
        $participant->setPseudo($faker->userName);
        $participant->setNom($faker->lastName);
        $participant->setPrenom($faker->firstName);
        $participant->setTelephone($faker->phoneNumber);
        $participant->setEmail($faker->freeEmail);
        $password = $this->hasher->hashPassword($participant,'Pa$$w0rd');
        $participant->setPassword($password);
        $participant->setAdministrateur(false);
        $participant->setRoles(["ROLE_USER"]);
        $participant->setActif(true);
        $participant->setCampus($campus);
        $manager->persist($participant);

        $fakerPostCode = Faker\Provider\Address::postcode();
        $ville = new Ville();
        $ville->setNom($faker->city);
        $ville->setCodePostal($fakerPostCode);
        $manager->persist($ville);

        $lieu = new Lieu();
        $lieu->setNom('chacun chez soi');
        $lieu->setRue($faker->streetAddress);
        $lieu->setLatitude($faker->latitude);
        $lieu->setLongitude($faker->longitude);
        $lieu->setVille($ville);
        $manager->persist($lieu);

        $sortie = new Sortie();
        $sortie->setNom('Tournoi de Fortnite');
        $dateDebut = $faker->dateTimeBetween('- 3 months', '+ 1 month');
        $sortie->setDateHeureDebut($dateDebut);
        $sortie->setDateLimiteInscription($faker->dateTimeInInterval($dateDebut,'-1 week'));
        $sortie->setDuree($faker->randomNumber(3,false));
        $sortie->setNbInscriptionMax($faker->randomNumber(2,false));
        $sortie->setCampus($campus);
        if ($dateDebut < $today){
            $etat = new Etat();
            $etat->setLibelle('Passée');
            $manager->persist($etat);
            $sortie->setEtat($etat);
        } elseif ($dateDebut->format('d/m/y') === $today->format('d/m/y')){
            $etat = new Etat();
            $etat->setLibelle('Activité en cours');
            $manager->persist($etat);
            $sortie->setEtat($etat);
        } else {
            $etat = new Etat();
            $etat->setLibelle('Ouverte');
            $manager->persist($etat);
            $sortie->setEtat($etat);
        }
        $sortie->setOrganisateur($participant);
        $sortie->setLieu($lieu);
        $sortie->setInfosSortie('Bon ok j\'avoue c\'est pas vraiement une sortie... ! :-)');
        $manager->persist($sortie);

        /////////////////////////// 4 ////////////////////////////////////////
        $participant = new Participant();
        $participant->setPseudo($faker->userName);
        $participant->setNom($faker->lastName);
        $participant->setPrenom($faker->firstName);
        $participant->setTelephone($faker->phoneNumber);
        $participant->setEmail($faker->freeEmail);
        $password = $this->hasher->hashPassword($participant,'Pa$$w0rd');
        $participant->setPassword($password);
        $participant->setAdministrateur(false);
        $participant->setRoles(["ROLE_USER"]);
        $participant->setActif(true);
        $participant->setCampus($campus);
        $manager->persist($participant);

        $fakerPostCode = Faker\Provider\Address::postcode();
        $ville = new Ville();
        $ville->setNom($faker->city);
        $ville->setCodePostal($fakerPostCode);
        $manager->persist($ville);

        $lieu = new Lieu();
        $lieu->setNom('parc des rives du fleuve');
        $lieu->setRue($faker->streetAddress);
        $lieu->setLatitude($faker->latitude);
        $lieu->setLongitude($faker->longitude);
        $lieu->setVille($ville);
        $manager->persist($lieu);

        $sortie = new Sortie();
        $sortie->setNom('Pique-nique au parc');
        $dateDebut = $faker->dateTimeBetween('- 3 months', '+ 1 month');
        $sortie->setDateHeureDebut($dateDebut);
        $sortie->setDateLimiteInscription($faker->dateTimeInInterval($dateDebut,'-1 week'));
        $sortie->setDuree($faker->randomNumber(3,false));
        $sortie->setNbInscriptionMax($faker->randomNumber(2,false));
        $sortie->setCampus($campus);
        if ($dateDebut < $today){
            $etat = new Etat();
            $etat->setLibelle('Passée');
            $manager->persist($etat);
            $sortie->setEtat($etat);
        } elseif ($dateDebut->format('d/m/y') === $today->format('d/m/y')){
            $etat = new Etat();
            $etat->setLibelle('Activité en cours');
            $manager->persist($etat);
            $sortie->setEtat($etat);
        } else {
            $etat = new Etat();
            $etat->setLibelle('Ouverte');
            $manager->persist($etat);
            $sortie->setEtat($etat);
        }
        $sortie->setOrganisateur($participant);
        $sortie->setLieu($lieu);
        $sortie->setInfosSortie('Si le temps le permet, retrouvons-nous au parc pour passer un moment sympathique');
        $manager->persist($sortie);

        /////////////////////////// 5 ////////////////////////////////////////
        $participant = new Participant();
        $participant->setPseudo($faker->userName);
        $participant->setNom($faker->lastName);
        $participant->setPrenom($faker->firstName);
        $participant->setTelephone($faker->phoneNumber);
        $participant->setEmail($faker->freeEmail);
        $password = $this->hasher->hashPassword($participant,'Pa$$w0rd');
        $participant->setPassword($password);
        $participant->setAdministrateur(false);
        $participant->setRoles(["ROLE_USER"]);
        $participant->setActif(true);
        $participant->setCampus($campus);
        $manager->persist($participant);

        $fakerPostCode = Faker\Provider\Address::postcode();
        $ville = new Ville();
        $ville->setNom($faker->city);
        $ville->setCodePostal($fakerPostCode);
        $manager->persist($ville);

        $lieu = new Lieu();
        $lieu->setNom('Centre ville');
        $lieu->setRue($faker->streetAddress);
        $lieu->setLatitude($faker->latitude);
        $lieu->setLongitude($faker->longitude);
        $lieu->setVille($ville);
        $manager->persist($lieu);

        $sortie = new Sortie();
        $sortie->setNom('Course de 5km pour l\'association ciel bleu');
        $dateDebut = $faker->dateTimeBetween('- 3 months', '+ 1 month');
        $sortie->setDateHeureDebut($dateDebut);
        $sortie->setDateLimiteInscription($faker->dateTimeInInterval($dateDebut,'-1 week'));
        $sortie->setDuree($faker->randomNumber(3,false));
        $sortie->setNbInscriptionMax($faker->randomNumber(2,false));
        $sortie->setCampus($campus);
        if ($dateDebut < $today){
            $etat = new Etat();
            $etat->setLibelle('Passée');
            $manager->persist($etat);
            $sortie->setEtat($etat);
        } elseif ($dateDebut->format('d/m/y') === $today->format('d/m/y')){
            $etat = new Etat();
            $etat->setLibelle('Activité en cours');
            $manager->persist($etat);
            $sortie->setEtat($etat);
        } else {
            $etat = new Etat();
            $etat->setLibelle('Ouverte');
            $manager->persist($etat);
            $sortie->setEtat($etat);
        }
        $sortie->setOrganisateur($participant);
        $sortie->setLieu($lieu);
        $sortie->setInfosSortie('Course caritative pour soutenir l\'association. A vos baskets !');
        $manager->persist($sortie);

        $manager->flush();
    }


}
