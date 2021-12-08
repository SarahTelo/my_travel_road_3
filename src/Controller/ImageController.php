<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Travel;
use App\Entity\Step;
use App\Form\ImageType;
use App\Repository\StepRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\FileUploader;
use App\Service\ProcessFormService;

/**
 * @Route("/api", name="images")
 */
class ImageController extends AbstractController
{
    private $fileUploader;
    private $processForm;

    public function __construct(
        FileUploader $fileUploader,
        ProcessFormService $processForm)
    {
        $this->fileUploader = $fileUploader;
        $this->processForm = $processForm;
    }

    /**
     * *Liste de toutes les images d'une étape
     * 
     * @Route("/step/{id}/images/", name="_list", methods={"GET"}, requirements={"id"="\d+"})
     * @param ImageRepository
     * @param StepRepository
     * @param Request $request
     * @return Response
     */
    public function list(Request $request): Response
    {
        $stepId = intval($request->attributes->get('id'));
        $step = $this->getDoctrine()->getRepository(Step::class)->find($stepId);
        if($step->getTravel()->getVisibility()) {
            $images = $this->getDoctrine()->getRepository(Image::class)->findBy(['step' => $stepId]);
        } else {
            return $this->json(['code' => 403, 'message' => 'Voyage inaccessible.'], Response::HTTP_FORBIDDEN);
        }
        return $this->json(['imageList' => $images], Response::HTTP_OK, [], ['groups' => 'image_list']);
    }

    /**
     * *Liste de toutes les images ADMIN
     * 
     * @Route("/admin/images/", name="_list_admin", methods={"GET"})
     * @param ImageRepository
     * @return Response
     */
    public function listAdmin(): Response
    {
        $images = $this->getDoctrine()->getRepository(Image::class)->findAll();
        return $this->json(['imageList' => $images], Response::HTTP_OK, [], ['groups' => 'image_list_admin']);
    }

    /**
     * *Ajout d'une image
     * 
     * @Route("/step/{id}/image/new/", name="_new", methods={"POST"}, requirements={"id"="\d+"})
     * @param FileUploader $fileUploader
     * @param ProcessFormService $processForm
     * @param StepRepository
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        /** @var StepRepository */
        $stepId = intval($request->attributes->get('id'));
        $step = $this->getDoctrine()->getRepository(Step::class)->find($stepId);
        $travel = $step->getTravel();
        $userStatus = $this->userStatus($travel);
        if(!$userStatus['isTravelOwner']) {
            if(!$userStatus['hasAdminAccess']) {
                $message = 'L\'utilisateur connecté n\'est pas le propriétaire du voyage à modifier.';
                return $this->json(['code' => 401, 'message' => $message], Response::HTTP_UNAUTHORIZED);
            }
        }

        //préparation des données
        $requestImageNew = $this->processForm->prepareDataImage($request->request->All());
        $arrayFile['path'] = $request->files->get('path');

        //formulaire
        $form = $this->createForm(ImageType::class, null, ['validation_groups' => 'constraints_new']);
        $form->submit($requestImageNew, false);

        //vérification des contraintes
        $errors = $this->processForm->validationForm($form, $arrayFile);
        if (!$form->isValid() || $errors != null) {
            if( $arrayFile['path'] === null) { $errors['image'][] = 'L\'ajout d\'une image nécessite un fichier.'; }
            return $this->json(['code' => 400, 'message' => $errors], Response::HTTP_BAD_REQUEST);
        }

        //création de l'image
        $image = $form->getData();
        $fileName = $this->fileUploader->upload($arrayFile['path']);
        $image->setPath($fileName);
        $image->setStep($step);
        if(!isset($requestImageNew['name'])) {
            $name = substr($fileName, 0, strripos($fileName, '.'));
            $image->setName($name);
        }

        //sauvegarde
        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($image);
            $em->flush();
        } catch (\Throwable $th) {
            $message = "L'image '{$requestImageNew['name']}' n'a pas pu être ajoutée. Veuillez contacter l'administrateur.";
            return $this->json(['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }
        return $this->json(['code' => 201, 'message' => ['image_id' => $image->getId()]], Response::HTTP_CREATED);
    }

    /**
     * *Modification d'une étape
     * 
     * @Route("/step/image/{id}/edit/", name="_edit", methods={"POST"}, requirements={"id"="\d+"})
     * @param FileUploader $fileUploader
     * @param ProcessFormService $processForm
     * @param StepRepository
     * @param Image $image
     * @param Request $request
     * @return Response
     */
    public function edit(Request $request, Image $image): Response
    {
        $step = $this->getDoctrine()->getRepository(Step::class)->find($image->getStep()->getId());
        $userStatus = $this->userStatus($step->getTravel());
        if(!$userStatus['isTravelOwner']) {
            if(!$userStatus['hasAdminAccess']) {
                $message = 'L\'utilisateur connecté n\'est pas le propriétaire du voyage à modifier.';
                return $this->json(['code' => 401, 'message' => $message], Response::HTTP_UNAUTHORIZED);
            }
        }

        //préparation des données
        $oldImagePath = $image->getPath();
        $requestImageEdit = $this->processForm->prepareDataImage($request->request->All());

        //formulaire
        $form = $this->createForm(ImageType::class, $image, ['validation_groups' => 'constraints_edit']);
        $form->submit($requestImageEdit, false);
        //vérification des contraintes
        $errors = $this->processForm->validationForm($form);
        if($request->files->get('path')) { $errors['path'][] = 'La photo ne doit pas être modifiée, sinon c\'est une nouvelle photo.'; }
        if (!$form->isValid() || $errors != null) {
            return $this->json(['code' => 400, 'message' => $errors], Response::HTTP_BAD_REQUEST);
        }

        //protection contre la suppression de l'ancienne image
        $image->setPath($oldImagePath);

        //sauvegarde
        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($image);
            $em->flush();
        } catch (\Throwable $th) {
            $message = "La photo '{$oldImagePath}' n'a pas pu être modifiée. Veuillez contacter l'administrateur.";
            return $this->json(['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }
        return $this->json(['code' => 200, 'message' => ['image_id' => $image->getId()]], Response::HTTP_OK);
    }

    /**
    * *Suppression d'une image
    * 
    * @Route("/step/image/{id}/delete/", name="_delete", methods={"DELETE"}, requirements={"id"="\d+"})
    * @param FileUploader $fileUploader
    * @param StepRepository
    * @param Image $image
    * @return Response
    */
    public function delete(Image $image): Response
    {
        $step = $this->getDoctrine()->getRepository(Step::class)->find($image->getStep()->getId());
        $userStatus = $this->userStatus($step->getTravel());
        if(!$userStatus['isTravelOwner']) {
            if(!$userStatus['hasAdminAccess']) {
                $message = 'L\'utilisateur connecté n\'est pas le propriétaire de la photo à supprimer.';
                return $this->json(['code' => 401, 'message' => $message], Response::HTTP_UNAUTHORIZED);
            }
        }

        //sauvegarde
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($image);
            $em->flush();
        } catch (\Throwable $th) {
            $message = "La photo <{$image->getName()}> n'a pas pu être supprimée. Veuillez contacter l'administrateur.";
            return $this->json(['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        //suppression des fichiers physiques
        $file = $image->getPath();
        if($file != null) {
            try{ $this->fileUploader->deleteFile($file); }
            catch(\Throwable $th) {
                $fileListToRemoveManually['files'] = $file; 
            }
        }
        //todo: envoyer un email aux administrateurs avec la liste: "$fileListToRemoveManually"
        return $this->json(['code' => 200, 'message' => 'deleted'], Response::HTTP_OK);
    }

    /**
     * *Tableau des autorisations de l'utilisateur
     *
     * @param Travel $travel
     * @return array
     */
    private function userStatus(Travel $travel): Array
    {
        $userStatus = [];

        //vérification si l'utilisateur connecté est bien le propriétaire du voyage à modifier
        $currentUserId = $this->getUser()->getId();
        $travelOwner = $travel->getUser()->getId();
        $currentUserId === $travelOwner ? $userStatus['isTravelOwner'] = true : $userStatus['isTravelOwner'] = false;
        $this->isGranted('ROLE_ADMIN') ? $userStatus['hasAdminAccess'] = true : $userStatus['hasAdminAccess'] = false;

        return $userStatus;
    }
}
