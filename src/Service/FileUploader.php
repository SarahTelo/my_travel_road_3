<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
//rajout
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FileUploader
{
    private $targetDirectory;
    private $slugger;
    //rajout
    private $validator;

    public function __construct($targetDirectory, SluggerInterface $slugger, ValidatorInterface $validator)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
        //rajout
        $this->validator = $validator;
    }
    
    /**
     * *Upload d'une image : transfert du fichier physique vers le dossier cible
     * 
     * @param file $file
     * @return string $fileName
     */
    public function upload(UploadedFile $file)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = strtolower($safeFilename.'-'.uniqid().'.'.$file->guessExtension());

        //* à décommenter pour sauvegarder physiquement
        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            throw new \Exception('Erreur dans le transfert du fichier vers sa destination.');
        }

        return $fileName;
    }

    /**
     * *Suppression du fichier physique
     *
     * @param string $fileName (propriété $thumbnail)
     * @return boolean
     */
    public function deleteFile (string $fileName) 
    {
        $path = $this->getTargetDirectory();
        //création du chemin complet
        $pathToRemove = $path . "/" . $fileName;
        
        //effacement du fichier physique s'il existe et s'il est dans le dossier spécifique
        if (file_exists($pathToRemove) && str_contains($path, '/public/assets/images')) 
        {
            try {
                //suppression du fichier
                unlink($pathToRemove);
            } catch (FileException $e) {
                throw new \Exception('Erreur dans la suppression du fichier.');
            }
            return true;
        } 
        else 
        {
            return false;
        }
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    /**
     * *Vérifications des contraintes d'une image
     *
     * @param ValidatorInterface $validator
     * @param file $imageData
     * @return array
     */
    public function imageContraints($imageData)
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
