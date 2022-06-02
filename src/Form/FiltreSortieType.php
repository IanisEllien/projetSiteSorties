<?php

namespace App\Form;

use App\Data\FiltreSortie;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('q', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'rechercher'
                ]
            ])
            ->add('campus', EntityType::class, [
                'label' => 'Campus',
                'required' => false,
                'class' => Campus::class,
                'choice_label' => 'nom'
            ])
            ->add('dateMin', DateType::class, [
                'label' => false,
                'required' => false,
                'html5' => true,
                'widget' => 'single_text'
            ])
            ->add('dateMax', DateType::class, [
                'label' => false,
                'required' => false,
                'html5' => true,
                'widget' => 'single_text'
            ])
            ->add('typeSortie', ChoiceType::class, [
                'label' => false,
                'required' => true,
                'choices' => [
                    'Sorties dont je suis l\'organisateur/trice' => 'orga' ,
                    'Sorties auxquelles je suis inscrit/e' => 'inscrit',
                    'Sorties auxquelles je ne suis pas inscrit/e' => 'noninscrit',
                    'Sorties passées' => 'finies'
                ],
                'choice_attr' => [
                    'Sorties dont je suis l\'organisateur/trice' => ['checked' => true],
                    'Sorties auxquelles je suis inscrit/e' => ['checked' => true],
                    'Sorties auxquelles je ne suis pas inscrit/e' => ['checked' => true]
                ],
                'expanded' =>true,
                'multiple' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FiltreSortie::class,
            'method' => 'GET',
            'crsf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
