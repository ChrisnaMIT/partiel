<?php

namespace App\Form;

use App\Entity\Film;
use App\Entity\Salle;
use App\Entity\Seance;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\DataTransformer\StringToTimeTransformer;


class SeanceForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de la séance',
            ])
            ->add('date', DateType::class, [
                'label' => 'Date de la séance',
                'widget' => 'single_text',
            ])
            ->add('startTime', ChoiceType::class, [
                'label' => 'Heure de la séance',
                'choices' => [
                    'Matin (10:30)' => '10:30:00',
                    'Après-midi 1 (13:45)' => '13:45:00',
                    'Après-midi 2 (16:30)' => '16:30:00',
                    'Soir 1 (19:00)' => '19:00:00',
                    'Soir 2 (22:15)' => '22:15:00',
                ],
                'placeholder' => 'Choisissez un horaire',
                'attr' => [
                    'class' => 'form-select',
                ],
            ])

            ->add('film', EntityType::class, [
                'class' => Film::class,
                'choice_label' => 'title',
                'label' => 'Film',
            ])
            ->add('salle', EntityType::class, [
                'class' => Salle::class,
                'choice_label' => 'numbre',
                'label' => 'Salle',
            ])
            ->add('placeAvailable', ChoiceType::class, [
                'label' => 'Nombre de places disponibles',
                'choices' => [
                    '45 places' => 45,
                    '85 places' => 85,
                ],
                'placeholder' => 'Sélectionnez un nombre de places',
                'attr' => [
                    'class' => 'form-select',
                ],
            ]);

        $builder->get('startTime')
            ->addModelTransformer(new StringToTimeTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Seance::class,
        ]);
    }
}
