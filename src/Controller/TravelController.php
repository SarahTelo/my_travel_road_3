<?php

namespace App\Controller;

use App\Entity\Travel;
use App\Entity\User;
use App\Form\TravelType;
use App\Repository\TravelRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\FileUploader;
use App\Service\AntiSpamService;
use App\Service\ProcessFormService;

/**
* @Route("/api", name="travels")
*/
class TravelController extends AbstractController
{
    private $fileUploader;
    private $validator;
    private $antiSpam;
    private $objectNormalizer;
    private $serializer;
    private $processForm;

    public function __construct( 
        FileUploader $fileUploader,
        ValidatorInterface $validator, 
        AntiSpamService $antiSpam,
        ObjectNormalizer $objectNormalizer, 
        SerializerInterface $serializer,
        ProcessFormService $processForm)
    {
        $this->serializer = $serializer;
        $this->objectNormalizer = $objectNormalizer;
        $this->fileUploader = $fileUploader;
        $this->validator = $validator;
        $this->antiSpam = $antiSpam;
        $this->processForm = $processForm;
    }

    /**
     * *Liste de tous les voyages avec leurs utilisateurs respectifs
     * 
     * @Route("/travels/", name="_list", methods={"GET"})
     * @Route("/admin/travels/", name="_list_admin", methods={"GET"})
     * @return Response
     */
    public function list(): Response
    {
        /** @var TravelRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Travel::class);

        if ($this->isGranted('ROLE_ADMIN')) {
            $travels = $repository->findAll();
            $groups = 'travel_list_admin';
        } else {
            $travels = $repository->findBy(
                ['visibility' => 1],
                ['created_at' => 'DESC']
            );
            $groups = 'travel_list_public';
        }
        return $this->json($travels, Response::HTTP_OK, [], ['groups' => $groups]);
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
        }
        return $this->json($travel, Response::HTTP_OK, [], ['groups' => $groups]);
    }

    /**
     * *Ajout d'un voyage
     * 
     * @Route("/travels/new/", name="_new", methods={"POST"})
     * @param ObjectNormalizer $objectNormalizer
     * @param FileUploader $fileUploader
     * @param AntiSpamService $antiSpam
     * @param ProcessFormService $processForm
     * @param Request $request
     * @return Response
     */
    public function new(Request $request) : Response
    {
        $contentType = $request->headers->get('Content-Type');
        if (!str_contains($contentType, 'multipart/form-data')) {
            return $this->json(['Nécessite \'multipart/form-data\' dans le header'], Response::HTTP_BAD_REQUEST);
        }

        //données de la requête
        $requestTravelNew = $request->request->All();
        //évite d'ajouter dans la db un string qui ne correspond à aucune image
        $requestTravelNew['cover'] = null;

        //pot de miel
        if(isset($requestTravelNew['_ne_rien_ajouter_']) && $this->antiSpam->antiSpam($requestTravelNew['_ne_rien_ajouter_'])) {
            return $this->json(['Qui êtes-vous?'], Response::HTTP_BAD_REQUEST);
        }
        
        $requestTravelNew['status'] = intval($requestTravelNew['status']);
        $requestTravelNew['visibility'] = boolval($requestTravelNew['visibility']);
        $fileCover = $request->files->get('cover');

        //vérification des erreurs
        $errors = $this->processForm->validationForm(Travel::class, $requestTravelNew, 'constraints_new', $fileCover);
        if (count($errors) > 0 ) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        //le voyage est créé ici
        $travel = $this->objectNormalizer->denormalize($requestTravelNew, Travel::class);
        $travel->setUser($this->getUser());
        //upload du fichier vers le dossier cible et récupération de son nom
        if (isset($fileCover)) { $travel->setCover($this->fileUploader->upload($fileCover)); }

        //sauvegarde
        $em = $this->getDoctrine()->getManager();
        try {
            $em->persist($travel);
            $em->flush();
        } catch (\Throwable $th) {
            $message[] = "Le voyage '{$requestTravelNew['title']}' n'a pas pu être ajouté. Veuillez contacter l'administrateur.";
            return $this->json( $message, Response::HTTP_SERVICE_UNAVAILABLE );
        }
        
        return $this->json(['created'], Response::HTTP_CREATED);
    }

    /**
     * *Modification d'un voyage
     * 
     * @Route("/travels/{id}/edit/", name="_edit", methods={"POST"})
     * @param ObjectNormalizer $objectNormalizer
     * @param FileUploader $fileUploader
     * @param AntiSpamService $antiSpam
     * @param ProcessFormService $processForm
     * @param Request $request
     * @param Travel $travel
     * @return Response
     */
    public function edit(Request $request, Travel $travel) : Response
    {
        $contentType = $request->headers->get('Content-Type');
        if (!str_contains($contentType, 'multipart/form-data')) {
            return $this->json(['Nécessite \'multipart/form-data\' dans le header'], Response::HTTP_BAD_REQUEST);
        }

        //données de la requête
        $requestTravelEdit = $request->request->All();
        //évite d'ajouter dans la db un string qui ne correspond à aucune image
        $requestTravelEdit['cover'] = null;

        //pot de miel
        if(isset($requestTravelEdit['_ne_rien_ajouter_']) && $this->antiSpam->antiSpam($requestTravelEdit['_ne_rien_ajouter_'])) {
            return $this->json(['Qui êtes-vous?'], Response::HTTP_BAD_REQUEST);
        }
        
        //caractéristiques de l'utilisateur connecté
        $userStatus = $this->userStatus($travel);
        if(!$userStatus['hasAdminAccess']) { 
            if(!$userStatus['isTravelOwner']) { 
                return $this->json(['L\'utilisateur connecté n\'est pas le propriétaire du voyage à modifier'], Response::HTTP_UNAUTHORIZED);
            }
        }

        if(isset($requestTravelEdit['status'])) { $requestTravelEdit['status'] = intval($requestTravelEdit['status']); }
        if(isset($requestTravelEdit['visibility'])) { $requestTravelEdit['visibility'] = boolval($requestTravelEdit['visibility']); }
        $fileCover = $request->files->get('cover');

        //vérification des erreurs
        $errors = $this->processForm->validationForm(Travel::class, $requestTravelEdit, 'constraints_edit', $fileCover);
        if (count($errors) > 0 ) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        //ajout des champs modifiés
        $form = $this->createForm(TravelType::class, $travel);
        $form->submit($requestTravelEdit, false);
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

        //sauvegarde
        $em = $this->getDoctrine()->getManager();
        try {
            $em->flush();
        } catch (\Throwable $th) {
            $message[] = "Le voyage '{$requestTravelEdit['title']}' n'a pas pu être ajouté. Veuillez contacter l'administrateur.";
            return $this->json( $message, Response::HTTP_SERVICE_UNAVAILABLE );
        }

        return $this->json(['updated'], Response::HTTP_CREATED);
    }

    /**
    * *Suppression d'un voyage
    * 
    * @Route("/travels/{id}/delete/", name="_delete", methods={"DELETE"}, requirements={"id"="\d+"})
    * @param Travel $travel
    * @param FileUploader $fileUploader
    * @param Resquest $request
    * @return Response
    */
    public function delete(Request $request, Travel $travel, UserPasswordHasherInterface $passwordHasher): Response
    {
        $requestDataToDeleteTravel = json_decode($request->getContent(), true);
        if(isset($requestDataToDeleteTravel['_ne_rien_ajouter_']) && $this->antiSpam->antiSpam($requestDataToDeleteTravel['_ne_rien_ajouter_'])) {
            return $this->json(['Qui êtes-vous?'], Response::HTTP_BAD_REQUEST);
        }
        //caractéristiques de l'utilisateur connecté
        $userStatus = $this->userStatus($travel);
        if(!$this->isGranted('ROLE_ADMIN')) { 
            if(!$userStatus['isTravelOwner']) { 
                return $this->json(['L\'utilisateur connecté n\'est pas le propriétaire du voyage à supprimer'], Response::HTTP_UNAUTHORIZED);
            } 
        }
        
        //vérification de l'ancier mot de passe
        $checkPassword = $passwordHasher->isPasswordValid($this->getUser(), $requestDataToDeleteTravel['password']);
        if (!$checkPassword) { 
            return $this->json(['Mot de passe incorrect'], Response::HTTP_BAD_REQUEST);
        }

        $title = $travel->getTitle();
        $travelCover = $travel->getCover();
        //si "not null": ça veut dire que l'image physique n'exite pas mais son chemin dans la db existe format 'string' (ce qui est effaçable via remove)
        $travelCover !== null ? $coverDeleted = $this->fileUploader->deleteFile($travelCover) : $coverDeleted = true;

        //sauvegarde
        $em = $this->getDoctrine()->getManager();
        //s'il n'y a plus d'images associées ou qu'elles ont bien été supprimées
        if(($travelCover === null || $coverDeleted)) {
            try {
                $em->remove($travel);
                $em->flush();
                $message[] = 'deleted';
                $statusCode = Response::HTTP_OK; 
            } catch (\Throwable $th) {
                $message[] = "L'utilisateur <{$title}> n'a pas pu être supprimé. Veuillez contacter l'administrateur.";
                $statusCode = Response::HTTP_SERVICE_UNAVAILABLE;
            }
        } else {
            $message[] = "L'utilisateur <{$title}> n'a pas été supprimé car le fichier physique '$travelCover' existe toujours.Veuillez contacter l'administrateur.";
            $statusCode = Response::HTTP_SERVICE_UNAVAILABLE;
        }

        return $this->json($message, $statusCode);
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
