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
                $lieux[$i] = new Lieu();
                $lieux[$i]->setNom($faker->words(2, true));
                $lieux[$i]->setRue($faker->streetAddress);
                $lieux[$i]->setLatitude($faker->latitude);
                $lieux[$i]->setLongitude($faker->longitude);
                $lieux[$i]->setVille($ville);

                $manager->persist( $lieux[$i]);

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

                $sorties[$i] = new Sortie();
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
                $manager->persist($sorties[$i]);
            }

        $lieu = new Lieu();
        $lieu->setNom('Pizzeria Da Enzo');
        $lieu->setRue('80 rue Saint Charles');
        $lieu->setLatitude(48.8473024);
        $lieu->setLongitude(2.2859873);
        $lieu->setVille($ville);
        $manager->persist($lieu);

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
        $sortie->setLieu($lieu);
        $sortie->setInfosSortie('Nous irons au musée Paul Gaugin afin d\'apprécier les oeuvres d\'art de ce grand peintre.');
        $manager->persist($sortie);

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

        $sortie = new Sortie();
        $sortie->setNom('Test : sortie en cours de création');
        $sortie->setDateHeureDebut(new \DateTime('2022-06-21 20:00:00'));
        $sortie->setDateLimiteInscription(new \DateTime('2022-06-14 17:00:00'));
        $sortie->setDuree(120);
        $sortie->setNbInscriptionMax(5);
        $sortie->setCampus($campus);
            $etat = new Etat();
            $etat->setLibelle('Créée');
            $manager->persist($etat);
        $sortie->setEtat($etat);
        $sortie->setOrganisateur($participant);
        $sortie->setLieu($lieu);
        $sortie->setInfosSortie('Profitons de ce solstice d\'été afin de se réunir autour d\'une bonne pizza chez Giovanni');
        $manager->persist($sortie);

        $manager->flush();
    }
}
