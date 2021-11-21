<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles')
            ->add('password')
            ->add('firstname')
            ->add('lastname')
            ->add('pseudo')
            ->add('presentation')
            ->add('_ne_rien_ajouter_', null, ['mapped' => false])
            ->add('checkPassword', null, ['mapped' => false])
            ->add('deleteAvatar', null, ['mapped' => false])
            ->add('deleteCover', null, ['mapped' => false])
            ->add('country', null, ['mapped' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => false,
        ]);
    }
}