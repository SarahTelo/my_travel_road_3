<?php

namespace App\Controller;

use App\Entity\Travel;
use App\Form\TravelType;
use App\Repository\TravelRepository;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\FileUploader;
use App\Service\ProcessFormService;
use DateTime;

/**
* @Route("/api", name="travels")
*/
class TravelController extends AbstractController
{
    private $fileUploader;
    private $objectNormalizer;
    private $processForm;
    private $passwordHasher;

    public function __construct( 
        FileUploader $fileUploader,
        ObjectNormalizer $objectNormalizer, 
        ProcessFormService $processForm,
        UserPasswordHasherInterface $passwordHasher)
    {
        $this->objectNormalizer = $objectNormalizer;
        $this->fileUploader = $fileUploader;
        $this->processForm = $processForm;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * *Liste de tous les voyages de l'utilisateur actuel (+ categories)
     * 
     * @Route("/travels/my-travels-list/", name="_list_private", methods={"GET"})
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
     * *Détail du voyage
     * 
     * @Route("/travels/{id}/detail/", name="_detail", methods={"GET"}, requirements={"id"="\d+"})
     * @param Travel $travel
     * @return Response
     */
    public function detail(Travel $travel): Response
    {
        $currentUser = $this->getUser();
        if ($currentUser->getId() === $travel->getUser()->getId()) {
            //sans l'utilisateur (propriétaire)
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
     * *Détail du voyage ADMIN
     * 
     * @Route("/admin/travels/{id}/detail/", name="_detail_admin", methods={"GET"}, requirements={"id"="\d+"})
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
     * @Route("/travels/new/", name="_new", methods={"POST"})
     * @param ObjectNormalizer $objectNormalizer
     * @param FileUploader $fileUploader
     * @param ProcessFormService $processForm
     * @param ObjectNormalizer $objectNormalizer
     * @param Request $request
     * @return Response
     */
    public function new(Request $request) : Response
    {
        //préparation des données
        $requestTravelNew = $request->request->All();
        //évite d'ajouter dans la db un string qui ne correspond à aucune image
        unset($requestTravelNew['cover']);
        $requestTravelNew['status'] = intval($requestTravelNew['status']);
        $requestTravelNew['visibility'] = boolval($requestTravelNew['visibility']);
        $fileCover = $request->files->get('cover');
        if (isset($requestTravelNew['categories'])) {
            $categories = $requestTravelNew['categories'];
            unset($requestTravelNew['categories']);
        }

        //vérification des erreurs
        $errors = $this->processForm->validationForm(Travel::class, $requestTravelNew, 'constraints_new', $fileCover);
        if (count($errors) > 0 ) {
            return $this->json(['code' => 400, 'message' => $errors], Response::HTTP_BAD_REQUEST);
        }

        //le voyage est créé ici
        $travel = $this->objectNormalizer->denormalize($requestTravelNew, Travel::class);
        $travel->setUser($this->getUser());
        //upload du fichier vers le dossier cible et récupération de son nom
        if (isset($fileCover)) { $travel->setCover($this->fileUploader->upload($fileCover)); }

        //gestion des catégories
        if (isset($categories) && count($categories) !== 0) {
            /** @var CategoryRepository $repository*/
            foreach ($categories as $value) {
                if ($value > 0) {
                    $category = $this->getDoctrine()->getRepository(Category::class)->find(intval($value));
                    $travel->addCategory($category);
                }
            }
        }

        //sauvegarde
        $em = $this->getDoctrine()->getManager();
        try {
            $em->persist($travel);
            $em->flush();
        } catch (\Throwable $th) {
            $message = "Le voyage '{$requestTravelNew['title']}' n'a pas pu être ajouté. Veuillez contacter l'administrateur.";
            return $this->json(['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }
        return $this->json(['code' => 201, 'message' => ['travel_id' => $travel->getId()]], Response::HTTP_CREATED);
    }

    /**
     * *Modification d'un voyage
     * 
     * @Route("/travels/{id}/edit/", name="_edit", methods={"POST"}, requirements={"id"="\d+"})
     * @param ObjectNormalizer $objectNormalizer
     * @param FileUploader $fileUploader
     * @param ProcessFormService $processForm
     * @param Request $request
     * @param Travel $travel
     * @return Response
     */
    public function edit(Request $request, Travel $travel) : Response
    {
        //caractéristiques de l'utilisateur connecté
        $userStatus = $this->userStatus($travel);
        if(!$userStatus['hasAdminAccess']) { 
            if(!$userStatus['isTravelOwner']) { 
                $message = 'L\'utilisateur connecté n\'est pas le propriétaire du voyage à modifier';
                return $this->json(['code' => 401, 'message' => $message], Response::HTTP_UNAUTHORIZED);
            }
        }

        //préparation des données
        $requestTravelEdit = $request->request->All();
        //évite d'ajouter dans la db un string qui ne correspond à aucune image
        unset($requestTravelEdit['cover']);
        if(isset($requestTravelEdit['status'])) { $requestTravelEdit['status'] = intval($requestTravelEdit['status']); }
        if(isset($requestTravelEdit['visibility'])) { $requestTravelEdit['visibility'] = boolval($requestTravelEdit['visibility']); }
        $fileCover = $request->files->get('cover');
        if (isset($requestTravelEdit['categories'])) {
            $categories = $requestTravelEdit['categories'];
            unset($requestTravelEdit['categories']);
        }

        //création d'un formulaire avec les anciennes données et vérification des contraintes
        $form = $this->createForm(TravelType::class, $travel);
        $entity = $this->processForm->validationFormEdit($form, $requestTravelEdit, 'cover', $fileCover);
        if(empty($entity['errors'])) {
            $travel = $form->getData();
        } else {
            return $this->json(['code' => 400, 'message' => $entity['errors']], Response::HTTP_BAD_REQUEST);
        }

        //gestion des dates
        if (isset($requestTravelEdit['start_at'])) { $travel->setStartAt(new DateTime($requestTravelEdit['start_at'])); }
        if (isset($requestTravelEdit['end_at'])) { $travel->setEndAt(new DateTime($requestTravelEdit['end_at'])); }

        //gestion des images
        if (isset($fileCover)) {
            //suppression de l'ancien fichier physique s'il existe
            if ($travel->getCover() != null) { $this->fileUploader->deleteFile($travel->getCover()); }
            //upload
            $newFilenameCover = $this->fileUploader->upload($fileCover);
            $travel->setCover($newFilenameCover);
        }
        //priorité au retrait de l'image si l'utilisateur ajoute une image ET coche "supprimer l'image"
        if (isset($requestTravelEdit['deleteCover'])) {
            //obligatoire de séparer
            $deleteCover = intval($requestTravelEdit['deleteCover']);
            if($deleteCover === 1) {
                $this->fileUploader->deleteFile($travel->getCover());
                $travel->setCover(null);
            }
        }

        //gestion des catégories
        if (isset($categories) && count($categories) !== 0) {
            /** @var CategoryRepository $repository*/
            foreach ($categories as $value) {
                if ($value > 0) {
                    $category = $this->getDoctrine()->getRepository(Category::class)->find(intval($value));
                    $travel->addCategory($category);
                }
            }
        }

        //sauvegarde
        $em = $this->getDoctrine()->getManager();
        try {
            $em->flush();
        } catch (\Throwable $th) {
            $message = "Le voyage '{$requestTravelEdit['title']}' n'a pas pu être ajouté. Veuillez contacter l'administrateur.";
            return $this->json(['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }
        return $this->json(['code' => 200, 'message' => 'updated'], Response::HTTP_OK);
    }

    /**
    * *Suppression d'un voyage
    * 
    * @Route("/travels/{id}/delete/", name="_delete", methods={"DELETE"}, requirements={"id"="\d+"})
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
            return $this->json(['code' => 400, 'message' => 'Qui êtes-vous?'], Response::HTTP_BAD_REQUEST);
        }
        //caractéristiques de l'utilisateur connecté
        $userStatus = $this->userStatus($travel);
        if(!$userStatus['hasAdminAccess']) { 
            if(!$userStatus['isTravelOwner']) { 
                $message = 'L\'utilisateur connecté n\'est pas le propriétaire du voyage à supprimer';
                return $this->json(['code' => 401, 'message' => $message], Response::HTTP_UNAUTHORIZED);
            } 
        }

        //vérification de l'ancier mot de passe
        $checkPassword = $this->passwordHasher->isPasswordValid($this->getUser(), $requestDataToDeleteTravel['password']);
        if (!$checkPassword) {
            return $this->json(['code' => 400, 'message' => 'Mot de passe incorrect'], Response::HTTP_BAD_REQUEST);
        }

        $title = $travel->getTitle();
        $travelCover = $travel->getCover();
        //si "not null": ça veut dire que l'image physique n'exite pas mais son chemin dans la db existe format 'string' (ce qui est effaçable via remove)
        $travelCover !== null ? $coverDeleted = $this->fileUploader->deleteFile($travelCover) : $coverDeleted = true;

        //s'il n'y a plus d'images associées ou qu'elles ont bien été supprimées
        if(($travelCover === null || $coverDeleted)) {
            try {
                $em = $this->getDoctrine()->getManager();
                $em->remove($travel);
                $em->flush();
                $message = 'deleted';
                $statusCode = Response::HTTP_OK; 
            } catch (\Throwable $th) {
                $message = "L'utilisateur <{$title}> n'a pas pu être supprimé. Veuillez contacter l'administrateur.";
                $statusCode = Response::HTTP_SERVICE_UNAVAILABLE;
            }
        } else {
            $message = "L'utilisateur <{$title}> n'a pas été supprimé car le fichier physique '$travelCover' existe toujours.Veuillez contacter l'administrateur.";
            $statusCode = Response::HTTP_SERVICE_UNAVAILABLE;
        }
        return $this->json(['code' => $statusCode, 'message' => $message], $statusCode);
    }

    /**
     * *Tableau des autorisations de l'utilisateur
     *
     * @param Entity $user
     * @return array
     */
    private function userStatus($travel) : Array
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
