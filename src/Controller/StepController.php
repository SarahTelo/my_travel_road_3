<?php

namespace App\Controller;

use App\Entity\Step;
use App\Form\StepType;
use App\Entity\Travel;
use App\Repository\TravelRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\FileUploader;
use App\Service\ProcessFormService;
use Symfony\Component\Serializer\SerializerInterface;

/**
* @Route("/api", name="steps")
*/
class StepController extends AbstractController
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
     * *Liste de toutes les étapes d'un voyage
     * 
     * @Route("/travel/{id}/steps/", name="_list", methods={"GET"}, requirements={"id"="\d+"})
     * @param Request $request
     * @return Response
     */
    public function list(Request $request): Response
    {
        /** @var TravelRepository */
        $travelId = intval($request->attributes->get('id'));
        $travel = $this->getDoctrine()->getRepository(Travel::class)->find($travelId);

        $dataSteps['travel'] = $travel;
        $dataSteps['stepsList'] = $this->getDoctrine()->getRepository(Step::class)->findBy(['travel' => $travelId], ['sequence' => 'ASC']);

        if($this->getUser()->getId() === $travel->getUser()->getId()) {
            $groups = 'step_list_private';
        } else {
            if($travel->getVisibility()) {
                $groups = 'step_list_public';
            } else {
                return $this->json(['code' => 403, 'message' => 'Voyage inaccessible.'], Response::HTTP_FORBIDDEN);
            }
        }
        return $this->json($dataSteps, Response::HTTP_OK, [], ['groups' => $groups]);
    }

    /**
     * *Liste de toutes les étapes d'un voyage ADMIN
     * 
     * @Route("/admin/travel/{id}/steps/", name="_list_admin", methods={"GET"}, requirements={"id"="\d+"})
     * @param Request $request
     * @return Response
     */
    public function listAdmin(Request $request): Response
    {
        /** @var TravelRepository */
        $travelId = intval($request->attributes->get('id'));
        $travel = $this->getDoctrine()->getRepository(Travel::class)->find($travelId);
        $dataSteps['travel'] = $travel;
        $dataSteps['stepsList'] = $this->getDoctrine()->getRepository(Step::class)->findBy(['travel' => $travelId], ['sequence' => 'ASC']);
        return $this->json($dataSteps, Response::HTTP_OK, [], ['groups' => 'step_list_admin']);
    }

    /**
     * *Détails d'une étape
     * 
     * @Route("/travel/step/{id}/detail/", name="_detail", methods={"GET"}, requirements={"id"="\d+"})
     * @param Step $step
     * @return Response
     */
    public function detail(Step $step): Response
    {
        /** @var TravelRepository */
        $travel = $this->getDoctrine()->getRepository(Travel::class)->find($step->getTravel()->getId());
        $dataSteps['travel'] = $travel;
        $dataSteps['step'] = $step;
        if($this->getUser()->getId() === $travel->getUser()->getId()) {
            $groups = 'step_detail_private';
        } else {
            if($travel->getVisibility()) {
                $groups = 'step_detail_public';
            } else {
                return $this->json(['code' => 403, 'message' => 'Voyage inaccessible.'], Response::HTTP_FORBIDDEN);
            }
        }
        return $this->json($dataSteps, Response::HTTP_OK, [], ['groups' => $groups]);
    }

    /**
     * *Détails d'une étape ADMIN
     * 
     * @Route("/admin/travel/step/{id}/detail/", name="_detail_admin", methods={"GET"}, requirements={"id"="\d+"})
     * @param Step $step
     * @return Response
     */
    public function detailAdmin(Step $step): Response
    {
        /** @var TravelRepository */
        $travel = $this->getDoctrine()->getRepository(Travel::class)->find($step->getTravel()->getId());
        $dataSteps['travel'] = $travel;
        $dataSteps['step'] = $step;
        return $this->json($dataSteps, Response::HTTP_OK, [], ['groups' => 'step_detail_admin']);
    }

    /**
     * *Ajout d'une étape
     * 
     * @Route("/travel/{id}/step/new/", name="_new", methods={"POST"}, requirements={"id"="\d+"})
     * @param FileUploader $fileUploader
     * @param ProcessFormService $processForm
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        /** @var TravelRepository */
        $travelId = intval($request->attributes->get('id'));
        $travel = $this->getDoctrine()->getRepository(Travel::class)->find($travelId);
        $userStatus = $this->userStatus($travel);
        if(!$userStatus['isTravelOwner']) {
            if(!$userStatus['hasAdminAccess']) {
                $message = 'L\'utilisateur connecté n\'est pas le propriétaire du voyage à modifier.';
                return $this->json(['code' => 401, 'message' => $message], Response::HTTP_UNAUTHORIZED);
            }
        }

        //préparation des données
        $requestStepNew = $this->processForm->prepareDataStep($request->request->All());
        $arrayFileCover['cover'] = $request->files->get('cover');

        //formulaire
        $form = $this->createForm(StepType::class, null, ['validation_groups' => 'constraints_new']);
        $form->submit($requestStepNew, false);

        //vérification des contraintes
        $errors = [];
        if (!$form->isValid()) {
            $errors = $this->processForm->validationForm($form, $arrayFileCover);
            if (!empty($errors)) {
                return $this->json(['code' => 400, 'message' => $errors], Response::HTTP_BAD_REQUEST);
            }
        }

        //création de l'étape
        $step = $form->getData();
        $previousStep = $this->getDoctrine()->getRepository(Step::class)->findOneBy(['travel' => $travelId], ['sequence' => 'DESC']);
        $step->setSequence($previousStep->getSequence() + 1);
        $step->setTravel($travel);
        if (isset($fileCover)) { $step->setCover($this->fileUploader->upload($fileCover)); }

        //sauvegarde
        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($step);
            $em->flush();
        } catch (\Throwable $th) {
            $message = "L'étape '{$requestStepNew['title']}' n'a pas pu être ajoutée. Veuillez contacter l'administrateur.";
            return $this->json(['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }
        return $this->json(['code' => 201, 'message' => ['step_id' => $step->getId()]], Response::HTTP_CREATED);
    }

    /**
     * *Modification d'une étape
     * 
     * @Route("/travel/step/{id}/edit/", name="_edit", methods={"POST"}, requirements={"id"="\d+"})
     * @param FileUploader $fileUploader
     * @param ProcessFormService $processForm
     * @param Step $step
     * @param Request $request
     * @return Response
     */
    public function edit(Request $request, Step $step): Response
    {
        $userStatus = $this->userStatus($step->getTravel());
        if(!$userStatus['isTravelOwner']) {
            if(!$userStatus['hasAdminAccess']) {
                $message = 'L\'utilisateur connecté n\'est pas le propriétaire du voyage à modifier.';
                return $this->json(['code' => 401, 'message' => $message], Response::HTTP_UNAUTHORIZED);
            }
        }

        //préparation des données
        $oldStepTitle = $step->getTitle();
        $requestStepEdit = $this->processForm->prepareDataStep($request->request->All());
        $arrayFileCover['cover'] = $request->files->get('cover');

        //formulaire
        $form = $this->createForm(StepType::class, $step, ['validation_groups' => 'constraints_edit']);
        $form->submit($requestStepEdit, false);
        //vérification des contraintes
        $errors = [];
        if (!$form->isValid()) {
            $errors = $this->processForm->validationForm($form, $arrayFileCover);
            if (!empty($errors)) {
                return $this->json(['code' => 400, 'message' => $errors], Response::HTTP_BAD_REQUEST);
            }
        }

        //gestion des images
        if (isset($fileCover)) {
            //suppression de l'ancien fichier physique s'il existe
            if ($step->getCover() != null) { $this->fileUploader->deleteFile($step->getCover()); }
            $newFilenameCover = $this->fileUploader->upload($fileCover);
            $step->setCover($newFilenameCover);
        }
        //priorité au retrait de l'image si l'utilisateur ajoute une image ET coche "supprimer l'image"
        if (isset($requestStepEdit['deleteCover'])) {
            //obligatoire de séparer
            $deleteCover = intval($requestStepEdit['deleteCover']);
            if($deleteCover === 1) {
                $this->fileUploader->deleteFile($step->getCover());
                $step->setCover(null);
            }
        }

        //sauvegarde
        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($step);
            $em->flush();
        } catch (\Throwable $th) {
            $message = "L'étape '{$oldStepTitle}' n'a pas pu être ajoutée. Veuillez contacter l'administrateur.";
            return $this->json(['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }
        return $this->json(['code' => 200, 'message' => ['step_id' => $step->getId()]], Response::HTTP_OK);
    }

    /**
    * *Suppression d'une étape
    * 
    * @Route("/travel/step/{id}/delete/", name="_delete", methods={"DELETE"}, requirements={"id"="\d+"})
    * @param FileUploader $fileUploader
    * @param Step $step
    * @return Response
    */
    public function delete(Step $step): Response
    {
        $userStatus = $this->userStatus($step->getTravel());
        if(!$userStatus['isTravelOwner']) {
            if(!$userStatus['hasAdminAccess']) {
                $message = 'L\'utilisateur connecté n\'est pas le propriétaire de l\'étape à supprimer.';
                return $this->json(['code' => 401, 'message' => $message], Response::HTTP_UNAUTHORIZED);
            }
        }

        //liste des noms des fichiers physiques à supprimer
        $fileList = [];
        $fileList[] = $step->getCover();
        //* à décommenter quand les images seront créées
        //!foreach ($step->getImages() as $file) { $fileList[] = $file->getPath(); }

        //sauvegarde
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($step);
            $em->flush();
        } catch (\Throwable $th) {
            $message = "L'étape <{$step->getTitle()}> n'a pas pu être supprimé. Veuillez contacter l'administrateur.";
            return $this->json(['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        //suppression des fichiers physiques
        foreach ($fileList as $file) { 
            if($file != null) { 
                try{ $this->fileUploader->deleteFile($file); }
                catch(\Throwable $th) { 
                    //$fileListToRemoveManually['step'] = $stepId; 
                    $fileListToRemoveManually['files'] = $file; 
                }
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
