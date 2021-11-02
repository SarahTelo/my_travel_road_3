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
     * @param Mixed $class
     * @param Mixed $requestBag
     * @param string $constraintsType
     * @param File $fileCover = null
     * @param ObjectNormalizer $objectNormalizer
     * @param ValidatorInterface $validator
     * @return Array
     */
    public function validationForm($class, $requestBag, string $constraintsType, $file = null) 
    {
        //vérifications des contraintes (assert et manuelles)
        $errors = [];
        //files
        $brutErrors = $this->imageContraints($file);
        foreach ($brutErrors as $value) { $errors[] = $value->getMessage(); }
        //data
        $entity = $this->objectNormalizer->denormalize($requestBag, $class);
        $brutErrors = $this->validator->validate($entity, null, $constraintsType);
        foreach ($brutErrors as $value) { $errors[] = $value->getMessage(); }

        return $errors;
    }

    
    /**
     * *Vérifications des contraintes d'une image
     *
     * @param ValidatorInterface $validator
     * @param file $imageData
     * @return array
     */
    private function imageContraints($imageData)
    {
        return $this->validator->validate($imageData, [
            new File([
                'maxSize' => '1M',
                'mimeTypes' => [ 'image/*' ],
                'mimeTypesMessage' => 'Le format du fichier envoyé doit être de type image.'
            ])
        ]);
    }
}