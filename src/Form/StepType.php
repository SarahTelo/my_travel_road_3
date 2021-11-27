<?php

namespace App\Form;

use App\Entity\Step;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('cover')
            ->add('description')
            ->add('start_coordinate')
            ->add('start_at')
            ->add('_ne_rien_ajouter_', null, ['mapped' => false])
            ->add('deleteCover', null, ['mapped' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Step::class,
            'csrf_protection' => false,
        ]);
    }
}
