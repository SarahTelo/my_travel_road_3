<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', null, [
                'invalid_message' => 'Le format n\'est pas valide.'
            ])
            ->add('password', null, [
                'invalid_message' => 'Le format n\'est pas valide.'
            ])
            ->add('roles', null, [
                'invalid_message' => 'Le format n\'est pas valide.'
            ])
            ->add('firstname', null, [
                'invalid_message' => 'Le format n\'est pas valide.'
            ])
            ->add('lastname', null, [
                'invalid_message' => 'Le format n\'est pas valide.'
            ])
            ->add('pseudo', null, [
                'invalid_message' => 'Le format n\'est pas valide.'
            ])
            ->add('presentation', null, [
                'invalid_message' => 'Le format n\'est pas valide.'
            ])
            ->add('country', null, [
                'invalid_message' => 'Le format n\'est pas valide.'
            ])
            ->add('checkPassword', null, [
                'mapped' => false
            ])
            ->add('deleteAvatar', null, [
                'mapped' => false
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
            'data_class' => User::class,
            'csrf_protection' => false,
            'extra_fields_message' => 'Le formulaire ne doit pas contenir de champs supplémentaires.'
        ]);
    }
}