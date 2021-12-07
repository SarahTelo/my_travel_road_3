<?php

namespace App\Form;

use App\Entity\Travel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TravelType extends AbstractType
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
            ->add('start_at', TextType::class, [
                'invalid_message' => 'Le format n\'est pas valide.'
            ])
            ->add('end_at', TextType::class, [
                'invalid_message' => 'Le format n\'est pas valide.'
            ])
            ->add('status', null, [
                'invalid_message' => 'Le format n\'est pas valide.'
            ])
            ->add('visibility', null, [
                'invalid_message' => 'Le format n\'est pas valide.'
            ])
            ->add('categories', null, [
                'invalid_message' => 'Le format n\'est pas valide.'
            ])
            ->add('deleteCover', null, [
                'mapped' => false
                ])
            ->add('_ne_rien_ajouter_', null, [
                'mapped' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Travel::class,
            'csrf_protection' => false,
            'extra_fields_message' => 'Le formulaire ne doit pas contenir de champs supplémentaires.'
        ]);
    }
}
