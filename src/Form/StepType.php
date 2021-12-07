<?php

namespace App\Form;

use App\Entity\Step;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class StepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'invalid_message' => 'Le format n\'est pas valide.'
            ])
            ->add('cover', null, [
                'invalid_message' => 'Le format n\'est pas valide.'
            ])
            ->add('description', null, [
                'invalid_message' => 'Le format n\'est pas valide.'
            ])
            ->add('start_coordinate', null, [
                'invalid_message' => 'Le format n\'est pas valide.'
            ])
            ->add('start_at', TextType::class, [
                'invalid_message' => 'Le format n\'est pas valide.'
            ])
            ->add('_ne_rien_ajouter_', null, [
                'mapped' => false
            ])
            ->add('deleteCover', null, [
                'mapped' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Step::class,
            'csrf_protection' => false,
            'extra_fields_message' => 'Le formulaire ne doit pas contenir de champs supplémentaires.'
        ]);
    }
}
