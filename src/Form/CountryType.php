<?php

namespace App\Form;

use App\Entity\Country;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',null, [
                'invalid_message' => 'Le format n\'est pas valide.'
            ])
            ->add('coordinate',null, [
                'invalid_message' => 'Le format n\'est pas valide.'
            ])
            ->add('_ne_rien_ajouter_', null, [
                'mapped' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Country::class,
            'csrf_protection' => false,
            'extra_fields_message' => 'Le formulaire ne doit pas contenir de champs supplémentaires.'
        ]);
    }
}
