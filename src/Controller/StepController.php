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
use DateTime;
use Symfony\Component\Serializer\SerializerInterface;

/**
* @Route("/api", name="steps")
*/
class StepController extends AbstractController
{
    private $fileUploader;
    private $processForm;
    private $serializer;

    public function __construct(
        FileUploader $fileUploader,
        ProcessFormService $processForm,
        SerializerInterface $serializer)
    {
        $this->fileUploader = $fileUploader;
        $this->processForm = $processForm;
        $this->serializer = $serializer;
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
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return Response
     */
    public function new(Request $request) : Response
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
        $requestStepNew = $request->request->All();
        $fileCover = $request->files->get('cover');
        //évite d'ajouter dans la db un string qui ne correspond à aucune image
        unset($requestStepNew['cover']);

        //création de l'objet et vérification des erreurs (méthode qui évite les erreurs liées au format des dates)
        $step = $this->serializer->deserialize(json_encode($requestStepNew), Step::class, 'json');
        $errors = $this->processForm->validationFormNew($step, 'constraints_new', $fileCover);
        if (count($errors) > 0 ) {
            return $this->json(['code' => 400, 'message' => $errors], Response::HTTP_BAD_REQUEST);
        }

        //ajout des éléments particuliers
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
    public function edit(Request $request, Step $step) : Response
    {
        $userStatus = $this->userStatus($step->getTravel());
        if(!$userStatus['isTravelOwner']) {
            if(!$userStatus['hasAdminAccess']) {
                $message = 'L\'utilisateur connecté n\'est pas le propriétaire du voyage à modifier.';
                return $this->json(['code' => 401, 'message' => $message], Response::HTTP_UNAUTHORIZED);
            }
        }

        //préparation des données
        $requestStepEdit = $request->request->All();
        $fileCover = $request->files->get('cover');
        //évite d'ajouter dans la db un string qui ne correspond à aucune image
        if (isset($requestStepEdit['cover'])) { unset($requestStepEdit['cover']); }

        //création d'un formulaire avec les anciennes données et vérification des contraintes
        $form = $this->createForm(StepType::class, $step);
        $entity = $this->processForm->validationFormEdit($form, $requestStepEdit, 'cover', $fileCover);
        if(empty($entity['errors'])) {
            $step = $form->getData();
        } else {
            return $this->json(['code' => 400, 'message' => $entity['errors']], Response::HTTP_BAD_REQUEST);
        }

        //gestion de la date
        if (isset($requestStepEdit['start_at'])) { $step->setStartAt(new DateTime($requestStepEdit['start_at'])); }

        //gestion des images
        if (isset($fileCover)) {
            //suppression de l'ancien fichier physique s'il existe
            if ($step->getCover() != null) { $this->fileUploader->deleteFile($step->getCover()); }
            //upload
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
            $message = "L'étape '{$requestStepEdit['title']}' n'a pas pu être ajoutée. Veuillez contacter l'administrateur.";
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
        $stepCover = $step->getCover();
        $stepCover !== null ? $coverDeleted = $this->fileUploader->deleteFile($stepCover) : $coverDeleted = true;

        if(($stepCover === null || $coverDeleted)) {
            try {
                $em = $this->getDoctrine()->getManager();
                $em->remove($step);
                $em->flush();
                $message = 'deleted';
                $statusCode = Response::HTTP_OK;
            } catch (\Throwable $th) {
                $message = "L'étape <{$step->getTitle()}> n'a pas pu être supprimé. Veuillez contacter l'administrateur.";
                $statusCode = Response::HTTP_SERVICE_UNAVAILABLE;
            }
        } else {
            $message = "L'étape <{$step->getTitle()}> n'a pas été supprimé car le fichier physique '$stepCover' existe toujours.Veuillez contacter l'administrateur.";
            $statusCode = Response::HTTP_SERVICE_UNAVAILABLE;
        }
        return $this->json(['code' => $statusCode, 'message' => $message], $statusCode);
    }

    /**
     * *Tableau des autorisations de l'utilisateur
     *
     * @param Travel $travel
     * @return array
     */
    private function userStatus(Travel $travel) : Array
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
