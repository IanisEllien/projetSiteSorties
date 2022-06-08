<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class InscriptionCsvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fichier', FileType::class,[
                'required' => true,
        'mapped' => false,
        'constraints'=>[
            new File([
                'maxSize'=>'5M',
                'mimeTypes'=>[
                    'text/x-comma-separated-values',
                    'text/comma-separated-values',
                    'text/x-csv',
                    'text/csv',
                    'text/plain',
                    'application/octet-stream',
                    'application/vnd.ms-excel',
                    'application/x-csv',
                    'application/csv',
                    'application/excel',
                    'application/vnd.msexcel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ],
                'mimeTypesMessage'=>'Veuillez choisir un fichier CSV',
            ])
        ]
    ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
