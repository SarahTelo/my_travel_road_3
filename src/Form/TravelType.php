<?php

namespace App\Form;

use App\Entity\Travel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TravelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('cover')
            ->add('description')
            ->add('start_at')
            ->add('end_at')
            ->add('status')
            ->add('visibility')
            ->add('_ne_rien_ajouter_', null, ['mapped' => false])
            ->add('deleteCover', null, ['mapped' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Travel::class,
            'csrf_protection' => false,
        ]);
    }
}
