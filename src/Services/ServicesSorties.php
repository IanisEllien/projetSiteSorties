<?php

namespace App\Services;

use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Common\Collections\ArrayCollection;
use phpDocumentor\Reflection\Types\Boolean;

class ServicesSorties
{
    public function estOrganisateur(Sortie $sortie, Participant $participant):bool{
        $estOrganisateur=false;

        $organisateur = $sortie->getOrganisateur();
        if ($organisateur === $participant){
            $estOrganisateur=true;
        }

        return $estOrganisateur;
    }

    public function estComplete (Sortie $sortie):bool{
        $estComplete = false;

        $nbParticipants = count($sortie->getParticipants());
        $nbInscriptionMax = $sortie->getNbInscriptionMax();

        if ($nbParticipants === $nbInscriptionMax){
            $estComplete = true;
        }
        return $estComplete;
    }

    public function rechercheParticipant(Participant $participant, $participants):bool{
            $present = false;
        foreach ($participants as $user){
            if ($user === $participant){
                $present=true;
            }
        }
        return $present;
    }
}