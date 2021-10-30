<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Repository\TravelRepository;
use App\Repository\StepRepository;
use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use App\Service\FileUploader;
use App\Service\AntiSpamService;

/**
* @Route("/api", name="users")
*/
class UserController extends AbstractController
{
    private $passwordHasher;
    private $fileUploader;
    private $validator;
    private $antiSpam;
    private $objectNormalizer;
    private $serializer;

    public function __construct( 
        UserPasswordHasherInterface $passwordHasher, 
        FileUploader $fileUploader,
        ValidatorInterface $validator, 
        AntiSpamService $antiSpam,
        ObjectNormalizer $objectNormalizer, 
        SerializerInterface $serializer )
    {
        $this->passwordHasher = $passwordHasher;
        $this->serializer = $serializer;
        $this->objectNormalizer = $objectNormalizer;
        $this->fileUploader = $fileUploader;
        $this->validator = $validator;
        $this->antiSpam = $antiSpam;
    }

    /**
     * *Liste des utilisateurs
     * 
     * @Route("/users/", name="_list", methods={"GET"})
     * @Route("/admin/users/", name="_list_admin", methods={"GET"})
     * @return Response
     */
    public function list(): Response
    {
        /** @var UserRepository $repository */
        $repository = $this->getDoctrine()->getRepository(User::class);

        if ($this->isGranted('ROLE_ADMIN')) {
            $users = $repository->findAll();
            $groups = 'user_list_admin';
        } else {
            $users = $repository->findBy(['id' => $this->getUser()->getId()]);
            $groups = 'user_list';
        }
        return $this->json($users, Response::HTTP_OK, [], ['groups' => $groups]);
    }

    /**
     * *Détail de l'utilisateur avec la liste de ses voyages
     * 
     * @Route("/users/{id}/detail/", name="_detail", methods={"GET"}, requirements={"id"="\d+"})
     * @param User $user
     * @return Response
     */
    public function detail(User $user): Response
    {
        //todo : rajouter la liste des voyages
        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user_detail']);
    }

    /**
     * *Profile de de l'utilisateur actuel avec la liste de ses voyages
     * 
     * @Route("/users/profile/", name="_profile", methods={"GET"})
     * @param User $user
     * @return Response
     */
    public function profile(): Response
    {
        $user = $this->getUser();
        //todo : rajouter la liste des voyages
        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user_profile']);
    }
    
    /**
     * *Ajout d'un utilisateur
     * 
     * @Route("/users/new/", name="_new", methods={"POST"})
     * @param ObjectNormalizer $objectNormalizer
     * @param ValidatorInterface $validator
     * @param FileUploader $fileUploader
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
        $requestBag = $request->request->All();

        //pot de miel
        if(isset($requestBag['_ne_rien_ajouter_']) && $this->antiSpam->antiSpam($requestBag['_ne_rien_ajouter_'])) {
            return $this->json(['Qui êtes-vous?'], Response::HTTP_BAD_REQUEST);
        }
        //vérification des erreurs
        $errors = $this->validationForm($request, $requestBag, 'constraints_new');
        if (count($errors) > 0 ) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        //l'utilisateur est créé ici
        $user = $this->objectNormalizer->denormalize($requestBag, User::class);
        //upload du fichier vers le dossier cible et récupération de son nom
        if (isset($fileCover)) { $user->setCover($this->fileUploader->upload($fileCover)); }
        if (isset($fileAvatar)) { $user->setAvatar($this->fileUploader->upload($fileAvatar)); }
        //encodage du mot de passe
        $user->setPassword($this->passwordHasher->hashPassword($user, $requestBag['password']));
        //vérification du rôle car l'utilisateur pourrait envoyer la clé "roles" dans le tableau de données reçues dans la $request
        $this->isGranted('ROLE_SUPER_ADMIN') ? $user->setRoles($requestBag['roles']) : $user->setRoles(['ROLE_USER']);

        //sauvegarde
        $em = $this->getDoctrine()->getManager();
        try {
            $em->persist($user);
            $em->flush();
        } catch (\Throwable $th) {
            $message[] = "L'utilisateur <{$requestBag['email']}> n'a pas pu être ajouté. Veuillez contacter l'administrateur.";
            return $this->json( $message, Response::HTTP_SERVICE_UNAVAILABLE );
        }
        
        return $this->json(['created'], Response::HTTP_CREATED);
    }

    /**
     * *Modification d'un utilisateur
     * 
     * @Route("/users/{id}/edit/", name="_edit", methods={"POST"})
     * @param ObjectNormalizer $objectNormalizer
     * @param ValidatorInterface $validator
     * @param FileUploader $fileUploader
     * @param Request $request
     * @return Response
     */
    public function edit(Request $request, User $user) : Response
    {
        $contentType = $request->headers->get('Content-Type');
        if (!str_contains($contentType, 'multipart/form-data')) {
            return $this->json(['Nécessite \'multipart/form-data\' dans le header'], Response::HTTP_BAD_REQUEST);
        }

        $requestBag = $request->request->All();
        if(isset($requestBag['_ne_rien_ajouter_']) && $this->antiSpam->antiSpam($requestBag['_ne_rien_ajouter_'])) {
            return $this->json(['Qui êtes-vous?'], Response::HTTP_BAD_REQUEST);
        }
        //caractéristiques de l'utilisateur connecté
        $userStatus = $this->userStatus($user);
        if(!$userStatus['hasAdminAccess']) { 
            unset($requestBag['roles']);
            if(!$userStatus['isCurrentUser']) { 
                return $this->json(['L\'utilisateur connecté n\'est pas le propriétaire du compte à modifier'], Response::HTTP_UNAUTHORIZED);
            }
        }
        //vérification de l'ancier mot de passe avant de supprimer l'entrée pour ne pas le modifier
        $checkPassword = $this->passwordHasher->isPasswordValid($user, $requestBag['password']);
        unset($requestBag['password']);
        //vérification des erreurs
        $errors = $this->validationForm($request, $requestBag, 'constraints_edit');
        if (!$checkPassword) { $errors[] = 'Mot de passe incorrect'; }
        if (count($errors) > 0 ) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        //ajout des champs modifiés
        $form = $this->createForm(UserType::class, $user);
        $form->submit($requestBag, false);
        //gestion des images
        $fileCover = $request->files->get('cover');
        $fileAvatar = $request->files->get('avatar');
        if (isset($fileCover)) {
            //suppression de l'ancien fichier physique s'il existe
            if ($user->getCover() != null) { $this->fileUploader->deleteFile($user->getCover()); }
            //upload
            $newFilenameCover = $this->fileUploader->upload($fileCover);
            $user->setCover($newFilenameCover);
        }
        if (isset($fileAvatar)) {
            if ($user->getAvatar() != null) { $this->fileUploader->deleteFile($user->getAvatar()); }
            $newFilenameAvatar = $this->fileUploader->upload($fileAvatar);
            $user->setAvatar($newFilenameAvatar);
        }

        try {
            $this->getDoctrine()->getManager()->flush();
        } catch (\Throwable $th) {
            $message[] = "L'utilisateur <{$requestBag['email']}> n'a pas pu être modifié. Veuillez contacter l'administrateur.";
            return $this->json( $message, Response::HTTP_SERVICE_UNAVAILABLE );
        }

        return $this->json(['updated'], Response::HTTP_OK);
    }

    /**
     * *Modification du mot de passe de l'utilisateur
     * 
     * @Route("/users/{id}/edit/password/", name="_edit_password", methods={"POST"})
     * @param ObjectNormalizer $objectNormalizer
     * @param ValidatorInterface $validator
     * @param FileUploader $fileUploader
     * @param Request $request
     * @return Response
     */
    public function editPassword(Request $request, User $user) : Response
    {
        $contentType = $request->headers->get('Content-Type');
        if (!str_contains($contentType, 'multipart/form-data')) {
            return $this->json(['Nécessite \'multipart/form-data\' dans le header'], Response::HTTP_BAD_REQUEST);
        }

        $requestBag = $request->request->All();
        if(isset($requestBag['_ne_rien_ajouter_']) && $this->antiSpam->antiSpam($requestBag['_ne_rien_ajouter_'])) {
            return $this->json(['Qui êtes-vous?'], Response::HTTP_BAD_REQUEST);
        }
        //caractéristiques de l'utilisateur connecté
        $userStatus = $this->userStatus($user);
        if(!$userStatus['isCurrentUser']) { 
            return $this->json(['L\'utilisateur connecté n\'est pas le propriétaire du compte à modifier'], Response::HTTP_UNAUTHORIZED);
        }
        //vérification des erreurs
        $errors = $this->validationForm($request, $requestBag, 'constraints_edit_password');
        $checkPassword = $this->passwordHasher->isPasswordValid($user, $requestBag['oldPassword']);
        if (!$checkPassword) { $errors[] = 'Ancien mot de passe incorrect'; }
        if (count($errors) > 0 ) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $requestBag['password']));

        try {
            $this->getDoctrine()->getManager()->flush();
        } catch (\Throwable $th) {
            return $this->json(
                ["L'utilisateur <{$requestBag['email']}> n'a pas pu être modifié. Veuillez contacter l'administrateur."], Response::HTTP_SERVICE_UNAVAILABLE
            );
        }
        
        return $this->json(['updated'], Response::HTTP_OK);
    }

    /**
    * *Suppression d'un utilisateur
    * 
    * @Route("/users/{id}/delete/", name="_delete", methods={"DELETE"}, requirements={"id"="\d+"})
    * @param User $user
    * @param FileUploader $fileUploader
    * @param Resquest $request
    * @return Response
    */
    public function userDelete(Request $request, User $user): Response
    {
        $requestBag = json_decode($request->getContent(), true);
        if(isset($requestBag['_ne_rien_ajouter_']) && $this->antiSpam->antiSpam($requestBag['_ne_rien_ajouter_'])) {
            return $this->json(['Qui êtes-vous?'], Response::HTTP_BAD_REQUEST);
        }
        //caractéristiques de l'utilisateur connecté
        $userStatus = $this->userStatus($user);
        if(!$this->isGranted('ROLE_SUPER_ADMIN')) { 
            if(!$userStatus['isCurrentUser']) { 
                return $this->json(['L\'utilisateur connecté n\'est pas le propriétaire du compte à supprimer'], Response::HTTP_UNAUTHORIZED);
            }
        }
        $errors = [];
        //vérification de l'ancier mot de passe
        $checkPassword = $this->passwordHasher->isPasswordValid($user, $requestBag['password']);
        if (!$checkPassword) { $errors[] = 'Mot de passe incorrect'; }

        $mail = $user->getEmail();
        $userCover = $user->getCover();
        $userAvatar = $user->getAvatar();
        //si "not null": ça veut dire que l'image physique n'exite pas mais son chemin dans la db existe format 'string' (ce qui est effaçable via remove)
        $userCover !== null ? $coverDeleted = $this->fileUploader->deleteFile($userCover) : $coverDeleted = true;
        $userAvatar !== null ? $avatarDeleted = $this->fileUploader->deleteFile($userAvatar) : $avatarDeleted = true;

        //sauvegarde
        $em = $this->getDoctrine()->getManager();
        //s'il n'y a plus d'images associées ou qu'elles ont bien été supprimées
        if((($userCover === null) && ($userAvatar === null)) || ($coverDeleted && $avatarDeleted)) {
            try {
                $em->remove($user);
                $em->flush();
                $message[] = 'deleted';
                $statusCode = Response::HTTP_OK; 
            } catch (\Throwable $th) {
                $message[] = "L'utilisateur <{$mail}> n'a pas pu être supprimé. Veuillez contacter l'administrateur.";
                $statusCode = Response::HTTP_SERVICE_UNAVAILABLE;
            }
        } else {
            $message[] = "L'utilisateur <{$mail}> n'a pas été supprimé car les fichiers physiques '$userCover' ou '$userAvatar' existent toujours.Veuillez contacter l'administrateur.";
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
    private function userStatus($user) : Array
    {
        $userStatus = [];

        //vérification si l'utilisateur connecté est bien le propriétaire du compte à modifier
        $currentUserId = $this->getUser()->getId();
        $userId = $user->getId();
        $currentUserId === $userId ? $userStatus['isCurrentUser'] = true : $userStatus['isCurrentUser'] = false;
        $this->isGranted('ROLE_ADMIN') ? $userStatus['hasAdminAccess'] = true : $userStatus['hasAdminAccess'] = false;

        return $userStatus;
    }

    /**
     * *Vérification des contraintes pour les champs du formulaire
     *
     * @param Request $request
     * @param Mixed $requestBag
     * @param string $constraints
     * @return Array
     */
    private function validationForm(Request $request, $requestBag, string $constraints) 
    {
        //vérifications des contraintes (assert et manuelles)
        $errors = [];
        //files
        $fileCover = $request->files->get('cover');
        $fileAvatar = $request->files->get('avatar');
        $brutErrors = $this->fileUploader->imageContraints($fileCover);
        foreach ($brutErrors as $value) { $errors[] = $value->getMessage(); }
        $brutErrors = $this->fileUploader->imageContraints($fileAvatar);
        foreach ($brutErrors as $value) { $errors[] = $value->getMessage(); }
        //data
        $user = $this->objectNormalizer->denormalize($requestBag, User::class);
        $brutErrors = $this->validator->validate($user, null, $constraints);
        foreach ($brutErrors as $value) { $errors[] = $value->getMessage(); }

        return $errors;
    }

}
