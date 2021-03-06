<?php

namespace App\Controller;

use App\Entity\Travel;
use App\Form\TravelType;
use App\Repository\TravelRepository;
use App\Entity\Step;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\FileUploader;
use App\Service\ProcessFormService;

/**
* @Route("/api", name="travels")
*/
class TravelController extends AbstractController
{
    private $fileUploader;
    private $processForm;
    private $passwordHasher;

    public function __construct(
        FileUploader $fileUploader,
        ProcessFormService $processForm,
        UserPasswordHasherInterface $passwordHasher)
    {
        $this->fileUploader = $fileUploader;
        $this->processForm = $processForm;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * *Liste de tous les voyages de l'utilisateur actuel (+ categories)
     * 
     * @Route("/my-travels-list/", name="_list_private", methods={"GET"})
     * @return Response
     */
    public function privateList(): Response
    {
        $travels = $this->getDoctrine()->getRepository(Travel::class)->findBy(['user' => $this->getUser()->getId()]);
        return $this->json(['travelList' => $travels], Response::HTTP_OK, [], ['groups' => 'travel_list_private']);
    }

    /**
     * *Liste de tous les voyages (user + categories)
     * 
     * @Route("/travels/", name="_list", methods={"GET"})
     * @return Response
     */
    public function list(): Response
    {
        /** @var TravelRepository */
        $travels = $this->getDoctrine()->getRepository(Travel::class)->findBy(
            ['visibility' => 1],
            ['created_at' => 'DESC']
        );
        return $this->json(['travelList' => $travels], Response::HTTP_OK, [], ['groups' => 'travel_list_public']);
    }

    /**
     * *Liste de tous les voyages (user + categories) ADMIN
     * 
     * @Route("/admin/travels/", name="_list_admin", methods={"GET"})
     * @return Response
     */
    public function listAdmin(): Response
    {
        /** @var TravelRepository */
        $travels = $this->getDoctrine()->getRepository(Travel::class)->findAll();
        return $this->json(['travelList' => $travels], Response::HTTP_OK, [], ['groups' => 'travel_list_admin']);
    }

    /**
     * *D??tail du voyage
     * 
     * @Route("/travel/{id}/detail/", name="_detail", methods={"GET"}, requirements={"id"="\d+"})
     * @param Travel $travel
     * @return Response
     */
    public function detail(Travel $travel): Response
    {
        $currentUser = $this->getUser();
        if ($currentUser->getId() === $travel->getUser()->getId()) {
            //sans le propri??taire
            $groups = 'travel_detail_private';
        } else {
            //avec l'utilisateur
            $groups = 'travel_detail_public';
            if(!$travel->getVisibility()) {
                return $this->json(['code' => 403, 'message' => 'Voyage inaccessible.'], Response::HTTP_FORBIDDEN);
            }
        }
        return $this->json($travel, Response::HTTP_OK, [], ['groups' => $groups]);
    }

    /**
     * *D??tail du voyage ADMIN
     * 
     * @Route("/admin/travel/{id}/detail/", name="_detail_admin", methods={"GET"}, requirements={"id"="\d+"})
     * @param Travel $travel
     * @return Response
     */
    public function detailAdmin(Travel $travel): Response
    {
        return $this->json($travel, Response::HTTP_OK, [], ['groups' => 'travel_detail_admin']);
    }

    /**
     * *Ajout d'un voyage
     * 
     * @Route("/travel/new/", name="_new", methods={"POST"})
     * @param FileUploader $fileUploader
     * @param ProcessFormService $processForm
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        //pr??paration des donn??es
        $requestTravelNew = $this->processForm->prepareDataTravel($request->request->All(), 'new');
        $arrayFileCover['cover'] = $request->files->get('cover');

        //formulaire
        $form = $this->createForm(TravelType::class, null, ['validation_groups' => 'constraints_new']);
        $form->submit($requestTravelNew, false);

        //v??rification des contraintes
        $errors = $this->processForm->validationForm($form, $arrayFileCover);
        if (!$form->isValid() || $errors != null) {
            return $this->json(['code' => 400, 'message' => $errors], Response::HTTP_BAD_REQUEST);
        }

        //cr??ation du voyage et de son ??tape de d??part
        $travel = $form->getData();
        $travel->setUser($this->getUser());
        //gestion de l'image
        if (isset($arrayFileCover['cover'])) {
            $newFilenameCover = $this->fileUploader->upload($arrayFileCover['cover']);
            $travel->setCover($newFilenameCover);
        }
        //gestion de l'??tape
        $step = (new Step())
            ->setTitle('D??part')
            ->setTravel($travel)
            ->setSequence(1);
        if ($travel->getStartAt() !== null) { $step->setStartAt($travel->getStartAt()); }

        //sauvegarde
        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($travel);
            $em->persist($step);
            $em->flush();
        } catch (\Throwable $th) {
            $message = "Le voyage '{$requestTravelNew['title']}' n'a pas pu ??tre ajout??. Veuillez contacter l'administrateur.";
            return $this->json(['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }
        return $this->json(['code' => 201, 'message' => ['travel_id' => $travel->getId(), 'step_id' => $step->getId()]], Response::HTTP_CREATED);
    }

    /**
     * *Modification d'un voyage
     * 
     * @Route("/travel/{id}/edit/", name="_edit", methods={"POST"}, requirements={"id"="\d+"})
     * @param FileUploader $fileUploader
     * @param ProcessFormService $processForm
     * @param Request $request
     * @param Travel $travel
     * @return Response
     */
    public function edit(Request $request, Travel $travel): Response
    {
        $userStatus = $this->userStatus($travel);
        if(!$userStatus['hasAdminAccess']) {
            if(!$userStatus['isTravelOwner']) {
                $message = 'L\'utilisateur connect?? n\'est pas le propri??taire du voyage ?? modifier';
                return $this->json(['code' => 401, 'message' => $message], Response::HTTP_UNAUTHORIZED);
            }
        }

        //pr??paration des donn??es
        $requestTravelEdit = $this->processForm->prepareDataTravel($request->request->All(), 'edit');
        $oldTravelTitle = $travel->getTitle();
        $arrayFileCover['cover'] = $request->files->get('cover');

        //formulaire
        $form = $this->createForm(TravelType::class, $travel, ['validation_groups' => 'constraints_edit']);
        $form->submit($requestTravelEdit, false);
        //v??rification des contraintes
        $errors = $this->processForm->validationForm($form, $arrayFileCover);
        if (!$form->isValid() || $errors != null) {
            return $this->json(['code' => 400, 'message' => $errors], Response::HTTP_BAD_REQUEST);
        }

        //mise ?? jour de la date de d??part de l'??tape 1 si l'utilisateur ne l'a pas effac??
        /** @var StepRepository */
        $firstStep = $this->getDoctrine()->getRepository(Step::class)->findOneBy(['travel' => $travel->getId(),'sequence' => 1]);
        if ($firstStep) { $firstStep->setStartAt($travel->getStartAt()); }

        //gestion des images
        if (isset($arrayFileCover['cover'])) {
            //suppression de l'ancien fichier physique s'il existe
            if ($travel->getCover() != null) { $this->fileUploader->deleteFile($travel->getCover()); }
            $newFilenameCover = $this->fileUploader->upload($arrayFileCover['cover']);
            $travel->setCover($newFilenameCover);
        }
        //priorit?? au retrait de l'image si l'utilisateur ajoute une image ET coche "supprimer l'image"
        if (isset($requestTravelEdit['deleteCover'])) {
            //obligatoire de s??parer
            $deleteCover = boolval($requestTravelEdit['deleteCover']);
            if($deleteCover) {
                $this->fileUploader->deleteFile($travel->getCover());
                $travel->setCover(null);
            }
        }

        //sauvegarde
        try {
            $this->getDoctrine()->getManager()->flush();
        } catch (\Throwable $th) {
            $message = "Le voyage '{$oldTravelTitle}' n'a pas pu ??tre modifi??. Veuillez contacter l'administrateur.";
            return $this->json(['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }
        return $this->json(['code' => 200, 'message' => 'updated'], Response::HTTP_OK);
    }

    /**
    * *Suppression d'un voyage
    * 
    * @Route("/travel/{id}/delete/", name="_delete", methods={"DELETE"}, requirements={"id"="\d+"})
    * @param Travel $travel
    * @param FileUploader $fileUploader
    * @param UserPasswordHasherInterface $passwordHasher
    * @param Resquest $request
    * @return Response
    */
    public function delete(Request $request, Travel $travel): Response
    {
        $requestDataToDeleteTravel = json_decode($request->getContent(), true);
        if(isset($requestDataToDeleteTravel['_ne_rien_ajouter_']) && $requestDataToDeleteTravel['_ne_rien_ajouter_'] != null) {
            return $this->json(['code' => 400, 'message' => 'Qui ??tes-vous?'], Response::HTTP_BAD_REQUEST);
        }
        //caract??ristiques de l'utilisateur connect??
        $userStatus = $this->userStatus($travel);
        if(!$userStatus['hasAdminAccess']) { 
            if(!$userStatus['isTravelOwner']) { 
                $message = 'L\'utilisateur connect?? n\'est pas le propri??taire du voyage ?? supprimer';
                return $this->json(['code' => 401, 'message' => $message], Response::HTTP_UNAUTHORIZED);
            } 
        }

        //v??rification de l'ancien mot de passe
        $checkPassword = $this->passwordHasher->isPasswordValid($this->getUser(), $requestDataToDeleteTravel['password']);
        if (!$checkPassword) {
            return $this->json(['code' => 400, 'message' => 'Mot de passe incorrect'], Response::HTTP_BAD_REQUEST);
        }

        //liste des noms des fichiers physiques ?? supprimer
        $fileList = [];
        $fileList[] = $travel->getCover();
        foreach ($travel->getSteps() as $step) { $fileList[] = $step->getCover(); }
        //* ?? rajouter dans le foreach
        //!foreach ($step->getImages() as $image) {
        //!    $fileList[] = $image->getPath(); 
        //!}

        //sauvegarde
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($travel);
            $em->flush();
        } catch (\Throwable $th) {
            $message = "Le voyage <{$travel->getTitle()}> n'a pas pu ??tre supprim??. Veuillez contacter l'administrateur.";
            return $this->json(['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        //suppression des fichiers physiques
        foreach ($fileList as $file) { 
            if($file != null) { 
                try{ $this->fileUploader->deleteFile($file); }
                catch(\Throwable $th) { 
                    //$fileListToRemoveManually['travel'] = $travelId; 
                    $fileListToRemoveManually['files'] = $file; 
                }
            }
        }
        //todo: envoyer un email aux administrateurs avec la liste: "$fileListToRemoveManually"
        return $this->json(['code' => 200, 'message' => 'deleted'], Response::HTTP_OK);
    }

    /**
     * *Tableau des autorisations de l'utilisateur actuel
     *
     * @param Travel $travel
     * @return array
     */
    private function userStatus($travel): Array
    {
        $userStatus = [];

        //v??rification si l'utilisateur connect?? est bien le propri??taire du voyage ?? modifier
        $currentUserId = $this->getUser()->getId();
        $travelOwner = $travel->getUser()->getId();
        $currentUserId === $travelOwner ? $userStatus['isTravelOwner'] = true : $userStatus['isTravelOwner'] = false;
        $this->isGranted('ROLE_ADMIN') ? $userStatus['hasAdminAccess'] = true : $userStatus['hasAdminAccess'] = false;

        return $userStatus;
    }
}
