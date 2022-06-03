<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => false,

            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => false,

                'data' => new \DateTime()
            ])


            ->add('lieu', LieuType::class)


                /*
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class
            ])
                */

            ->add('dateLimiteInscription', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => false,
                'data' => new \DateTime()
            ])
            ->add('nbInscriptionMax', NumberType::class, [
                'label' => false,
            ])
            ->add('duree', NumberType::class, [
                'label' => false,
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('enregistrerSortie', SubmitType::class, [
                'label' => 'Enregistrer'
            ])

            ->add('publierSortie', SubmitType::class, [
                'label' => 'Publier la sortie'
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
