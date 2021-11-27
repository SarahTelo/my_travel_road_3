<?php

namespace App\Service;

use App\Entity\Travel;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProcessFormService
{
    private $validator;
    
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * *Vérification des contraintes pour les champs du formulaire
     *
     * @param Mixed $entity
     * @param File $coverFile
     * @param File $avatarFile
     * @param ValidatorInterface $validator
     * @return Array
     */
    public function validationFormNew($entity, $coverFile = null, $avatarFile = null) : array
    {
        //vérifications des contraintes (assert et manuelles)
        $errors = [];
        //data
        $brutErrors = $this->validator->validate($entity, null, 'constraints_new');
        foreach ($brutErrors as $value) { $errors[$value->getPropertyPath()] = $value->getMessage(); }

        //file
        if (isset($coverFile)) {
            $brutErrors = $this->imageContraints($coverFile);
            foreach ($brutErrors as $value) { $errors['cover'] = $value->getMessage(); }
        }

        //avatar file
        if (isset($avatarFile)) {
            $brutErrors = $this->imageContraints($avatarFile);
            foreach ($brutErrors as $value) { $errors['avatar'] = $value->getMessage(); }
        }

        return $errors;
    }

    /**
     * *Vérification des contraintes pour les champs du formulaire mode édition
     *
     * @param Mixed $form
     * @param Mixed $requestBag
     * @param File $coverFile
     * @param File $avatarFile
     * @param ValidatorInterface $validator
     * @return Array
     */
    public function validationFormEdit($form, $requestBag, $coverFile = null, $avatarFile = null) : array
    {
        $errors = [];
        $data = ['entity' => [], 'errors' => []];

        //vérifications des contraintes (assert et manuelles)
        $form->submit($requestBag, false);
        $entityErrors = $this->validator->validate($form->getNormData(), null, ['constraints_edit']);
        foreach ($entityErrors as $value) { $errors[$value->getPropertyPath()] = $value->getMessage(); }

        if(count($entityErrors) === 0 && $form->isValid()) {
            //l'objet initial récupère les nouvelles données
            $entity = $form->getData();
            $data['entity'] = $entity;
        } else {
            //erreurs liées au formulaire en lui même (extra_fields par exemple)
            $formErrors = $form->getErrors();
            foreach ($formErrors as $value) { $errors['formError'] = $value->getMessage(); }
            $data['errors'] = $errors;
        }

        if (isset($coverFile)) {
            $brutErrors = $this->imageContraints($coverFile);
            foreach ($brutErrors as $value) { $data['errors']['cover'] = $value->getMessage(); }
        }

        //avatar file
        if (isset($avatarFile)) {
            $brutErrors = $this->imageContraints($avatarFile);
            foreach ($brutErrors as $value) { $data['errors']['avatar'] = $value->getMessage(); }
        }

        return $data;
    }

    /**
     * *Vérifications des contraintes d'une image
     *
     * @param ValidatorInterface $validator
     * @param file $file
     * @return array
     */
    private function imageContraints($file)
    {
        return $this->validator->validate($file, [
            new File([
                'maxSize' => '1M',
                'mimeTypes' => [ 'image/*' ],
                'mimeTypesMessage' => 'Le format du fichier envoyé doit être de type image.'
            ])
        ]);
    }
}