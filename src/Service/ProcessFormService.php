<?php

namespace App\Service;

use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use DateTime;

class ProcessFormService
{
    private $validator;
    
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Vérification des contraintes liées au formulaire et à l'objet (entity)
     *
     * @param object $form
     * @param array $fileArray
     * @return array
     */
    public function validationForm(Object $form, Array $fileArray = null): Array
    {
        foreach ($form->getErrors(true) as $value) {
            $property = $value->getOrigin()->getName();
            $errors[$property][] = $value->getMessage();
        }
        if ($fileArray !== null) {
            foreach ($fileArray as $property => $file) {
                $brutErrors = $this->imageContraints($file);
                foreach ($brutErrors as $value) { $errors[$property][] = $value->getMessage(); }
            }
        }

        return $errors;
    }

    /**
     * Préparation des donnés d'un utilisateur
     *
     * @param array $requestUser
     * @return array
     */
    public function prepareDataUser(Array $requestUser, $type = null): array
    {
        if($type === 'edit') {
            unset($requestUser['password']);
        }
        unset($requestUser['cover']);
        unset($requestUser['avatar']);
        return $requestUser;
    }

    /**
     * Préparation des donnés de voyage
     *
     * @param array $requestTravel
     * @param string $type
     * @return array
     */
    public function prepareDataTravel(Array $requestTravel, string $type = null): array
    {   
        if (isset($requestTravel['start_at'])) { $requestTravel['start_at'] = new DateTime($requestTravel['start_at']); }
        if (isset($requestTravel['end_at'])) { $requestTravel['end_at'] = new DateTime($requestTravel['end_at']); }
        //évite d'ajouter dans la db un string qui ne correspond à aucune image
        if (isset($requestTravel['cover'])) { unset($requestTravel['cover']); }
        if (isset($requestTravel['categories']) && count($requestTravel['categories']) !== 0) {
            $categories = $requestTravel['categories'];
            foreach ($categories as $key => $category) { $categories[$key] = intval($category); }
        }
        if ($type !== null && $type === 'new') {
            $requestTravel['visibility'] = boolval($requestTravel['visibility']);
        } else {
            if (isset($requestTravel['visibility'])) { $requestTravel['visibility'] = boolval($requestTravel['visibility']); }
        }
        return $requestTravel;
    }

    /**
     * Préparation des donnés d'une étape
     *
     * @param array $requestStep
     * @return array
     */
    public function prepareDataStep(Array $requestStep): array
    {
        //évite d'ajouter dans la db un string qui ne correspond à aucune image
        if (isset($requestStep['cover'])) { unset($requestStep['cover']); }
        if (isset($requestStep['start_at'])) { $requestStep['start_at'] = new DateTime($requestStep['start_at']); }
        return $requestStep;
    }

    /**
     * Vérifications des contraintes d'une image
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