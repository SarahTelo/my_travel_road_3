<?php

namespace App\Service;

use App\Entity\Travel;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ProcessFormService
{
    private $validator;
    private $objectNormalizer;
    
    public function __construct( 
        ValidatorInterface $validator,
        ObjectNormalizer $objectNormalizer )
    {
        $this->objectNormalizer = $objectNormalizer;
        $this->validator = $validator;
    }

    /**
     * *Vérification des contraintes pour les champs du formulaire
     *
     * @param Mixed $entity
     * @param string $constraintsType
     * @param File $fileCover
     * @param ValidatorInterface $validator
     * @return Array
     */
    public function validationFormNew($entity, string $constraintsType, $file = null) : array
    {
        //vérifications des contraintes (assert et manuelles)
        $errors = [];
        //data
        $brutErrors = $this->validator->validate($entity, null, $constraintsType);
        foreach ($brutErrors as $value) { $errors[$value->getPropertyPath()] = $value->getMessage(); }
        //file
        $brutErrors = $this->imageContraints($file);
        foreach ($brutErrors as $value) { $errors['cover'] = $value->getMessage(); }

        return $errors;
    }

    /**
     * *Vérification des contraintes pour les champs du formulaire mode édition
     *
     * @param Mixed $class
     * @param Mixed $requestBag
     * @param string $propertyFileName
     * @param File $fileCover
     * @param ValidatorInterface $validator
     * @return Array
     */
    public function validationFormEdit($form, $requestBag, string $propertyFileName = null, $file = null) : array
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

        //file
        $brutErrors = $this->imageContraints($file);
        foreach ($brutErrors as $value) { $data['errors'][$propertyFileName] = $value->getMessage(); }

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