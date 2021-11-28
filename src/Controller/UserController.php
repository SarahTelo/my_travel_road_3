<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Entity\Travel;
use App\Repository\TravelRepository;
use App\Entity\Country;
use App\Repository\CountryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use App\Service\FileUploader;
use App\Service\ProcessFormService;

/**
* @Route("/api", name="users")
*/
class UserController extends AbstractController
{
    private $passwordHasher;
    private $fileUploader;
    private $processForm;
    private $validator;
    private $objectNormalizer;
    private $serializer;

    public function __construct( 
        UserPasswordHasherInterface $passwordHasher, 
        FileUploader $fileUploader,
        ProcessFormService $processForm,
        ValidatorInterface $validator,
        ObjectNormalizer $objectNormalizer,
        SerializerInterface $serializer)
    {
        $this->passwordHasher = $passwordHasher;
        $this->objectNormalizer = $objectNormalizer;
        $this->fileUploader = $fileUploader;
        $this->processForm = $processForm;
        $this->validator = $validator;
        $this->serializer = $serializer;
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
        $users = $repository->findAll();

        if ($this->isGranted('ROLE_ADMIN')) {
            //$users = $repository->findAll();
            $groups = 'user_list_admin';
        } else {
            //$users = $repository->findBy(['id' => $this->getUser()->getId()]);
            $groups = 'user_list';
        }
        return $this->json(['userList' => $users], Response::HTTP_OK, [], ['groups' => $groups]);
    }

    /**
     * *Détail de l'utilisateur avec la liste de ses voyages visibles
     * 
     * @Route("/users/{id}/detail/", name="_detail", methods={"GET"}, requirements={"id"="\d+"})
     * @param User $user
     * @return Response
     */
    public function detail(User $user): Response
    {
        /** @var TravelRepository $repository*/
        $repository = $this->getDoctrine()->getRepository(Travel::class);
        $travels = $repository->findByUserAndVisibility($user->getId(), true);
        return $this->json([
            'userDetail' => $user,
            'travelsList' => $travels,
        ],
            Response::HTTP_OK, [], ['groups' => 'user_detail']
        );
    }

    /**
     * *Page de profil de l'utilisateur actuel
     * 
     * @Route("/users/profile/", name="_profile", methods={"GET"})
     * @param User $user
     * @return Response
     */
    public function profile(): Response
    {
        $user = $this->getUser();
        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user_profile']);
    }

    /**
     * *Ajout d'un utilisateur
     * 
     * @Route("/users/new/", name="_new", methods={"POST"})
     * @param ObjectNormalizer $objectNormalizer
     * @param UserPasswordHasherInterface $passwordHasher
     * @param FileUploader $fileUploader
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        //préparation des données
        $requestUserDataNew = $request->request->All();
        unset($requestUserDataNew['cover']);
        unset($requestUserDataNew['avatar']);
        if(isset($requestUserDataNew['country'])) {
            $country = $requestUserDataNew['country'];
            unset($requestUserDataNew['country']);
        }

        //création de l'objet et vérification des erreurs
        $user = $this->objectNormalizer->denormalize($requestUserDataNew, User::class);
        $fileCover = $request->files->get('cover');
        $fileAvatar = $request->files->get('avatar');
        $errors = $this->processForm->validationFormNew($user, $fileCover, $fileAvatar);
        if (count($errors) > 0 ) {
            return $this->json(['code' => 400, 'message' => $errors], Response::HTTP_BAD_REQUEST);
        }

        //gestion des images
        if (isset($fileCover)) { $user->setCover($this->fileUploader->upload($fileCover)); }
        if (isset($fileAvatar)) { $user->setAvatar($this->fileUploader->upload($fileAvatar)); }

        //gestion du mot de passe et des rôles
        $user->setPassword($this->passwordHasher->hashPassword($user, $requestUserDataNew['password']));
        $this->isGranted('ROLE_SUPER_ADMIN') ? $user->setRoles($requestUserDataNew['roles']) : $user->setRoles(['ROLE_USER']);

        //gestion du pays
        if (isset($country) && intval($country) != 0) { 
            $countryId = intval($country);
            /** @var CountryRepository $repository*/
            $country = $this->getDoctrine()->getRepository(Country::class)->find($countryId);
            $user->setCountry($country);
        } else {
            $user->setCountry(null);
        }

        //sauvegarde
        $em = $this->getDoctrine()->getManager();
        try {
            $em->persist($user);
            $em->flush();
        } catch (\Throwable $th) {
            $message = "L'utilisateur <{$requestUserDataNew['email']}> n'a pas pu être ajouté. Veuillez contacter l'administrateur.";
            return $this->json(['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return $this->json(['code' => 201, 'message' => ['user_id' => $user->getId()]], Response::HTTP_CREATED);
    }

    /**
     * *Modification d'un utilisateur
     * 
     * @Route("/users/{id}/edit/", name="_edit", methods={"POST"}, requirements={"id"="\d+"})
     * @param UserPasswordHasherInterface $passwordHasher
     * @param FileUploader $fileUploader
     * @param User $user
     * @param Request $request
     * @return Response
     */
    public function edit(Request $request, User $user): Response
    {
        $requestUserDataEdit = $request->request->All();

        //caractéristiques de l'utilisateur connecté
        $userStatus = $this->userStatus($user);
        if(!$userStatus['hasAdminAccess']) { 
            unset($requestUserDataEdit['roles']);
            if(!$userStatus['isCurrentUser']) { 
                $message = 'L\'utilisateur connecté n\'est pas le propriétaire du compte à modifier';
                return $this->json(['code' => 401, 'message' => $message], Response::HTTP_UNAUTHORIZED);
            }
        }

        $oldUserPseudo = $user->getPseudo();
        unset($requestUserDataEdit['cover']);
        unset($requestUserDataEdit['avatar']);
        unset($requestUserDataEdit['password']);

        //vérification des erreurs
        $fileCover = $request->files->get('cover');
        $fileAvatar = $request->files->get('avatar');

        $form = $this->createForm(UserType::class, $user);
        $entity = $this->processForm->validationFormEdit($form, $requestUserDataEdit, $fileCover, $fileAvatar);
        $checkPassword = $this->passwordHasher->isPasswordValid($this->getUser(), $requestUserDataEdit['checkPassword']);
        if (!$checkPassword) { $entity['errors']['password'] = 'Mot de passe incorrect.'; }
        if(empty($entity['errors'])) {
            $user = $form->getData();
            // OU BIEN: $user = $entity['entity'];
        } else {
            return $this->json(['code' => 400, 'message' => $entity['errors']], Response::HTTP_BAD_REQUEST);
        }

        //gestion des images
        if (isset($fileCover)) {
            //suppression de l'ancien fichier physique s'il existe
            if ($user->getCover() != null) { $this->fileUploader->deleteFile($user->getCover()); }
            $newFilenameCover = $this->fileUploader->upload($fileCover);
            $user->setCover($newFilenameCover);
        }
        if (isset($fileAvatar)) {
            if ($user->getAvatar() != null) { $this->fileUploader->deleteFile($user->getAvatar()); }
            $newFilenameAvatar = $this->fileUploader->upload($fileAvatar);
            $user->setAvatar($newFilenameAvatar);
        }
        //priorité au retrait de l'image si l'utilisateur ajoute une image ET coche "supprimer l'image"
        if (isset($requestUserDataEdit['deleteCover'])) {
            //obligatoire de séparer
            $deleteCover = intval($requestUserDataEdit['deleteCover']);
            if($deleteCover === 1) {
                $this->fileUploader->deleteFile($user->getCover());
                $user->setCover(null);
            }
        }
        if (isset($requestUserDataEdit['deleteAvatar'])) {
            $deleteAvatar = intval($requestUserDataEdit['deleteAvatar']);
            if($deleteAvatar === 1) {
                $this->fileUploader->deleteFile($user->getAvatar());
                $user->setAvatar(null);
            }
        }

        //gestion du pays
        if (isset($requestUserDataEdit['country']) && intval($requestUserDataEdit['country']) != 0) { 
            $countryId = intval($requestUserDataEdit['country']);
            /** @var CountryRepository $repository*/
            $country = $this->getDoctrine()->getRepository(Country::class)->find($countryId);
            $user->setCountry($country);
        }

        //sauvegarde
        try {
            $this->getDoctrine()->getManager()->flush();
        } catch (\Throwable $th) {
            $message = "L'utilisateur <{$oldUserPseudo}> n'a pas pu être modifié. Veuillez contacter l'administrateur.";
            return $this->json(['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }
        return $this->json(['code' => 200, 'message' => 'updated'], Response::HTTP_OK);
    }

    /**
     * *Modification du mot de passe de l'utilisateur
     * 
     * @Route("/users/{id}/edit/password/", name="_edit_password", methods={"POST"}, requirements={"id"="\d+"})
     * @param ValidatorInterface $validator
     * @param UserPasswordHasherInterface $passwordHasher
     * @param User $user
     * @param Request $request
     * @return Response
     */
    public function editPassword(Request $request, User $user): Response
    {
        $requestEditUserPassword = $request->request->All();
        //caractéristiques de l'utilisateur connecté
        $userStatus = $this->userStatus($user);
        if(!$userStatus['isCurrentUser']) { 
            $message = 'L\'utilisateur connecté n\'est pas le propriétaire du compte à modifier.';
            return $this->json(['code' => 401, 'message' => $message], Response::HTTP_UNAUTHORIZED);
        }

        //vérification des erreurs
        $errors = [];
        $userPasswordTest = (new User())->setPassword($requestEditUserPassword['password']);
        $brutErrors = $this->validator->validate($userPasswordTest, null, 'constraints_edit_password');
        foreach ($brutErrors as $value) { $errors['password'] = $value->getMessage(); }
        $checkPassword = $this->passwordHasher->isPasswordValid($this->getUser(), $requestEditUserPassword['oldPassword']);
        if (!$checkPassword) { $errors['oldPassword'] = 'Ancien mot de passe incorrect'; }
        if (count($errors) > 0 ) {
            return $this->json(['code' => 400, 'message' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $requestEditUserPassword['password']));

        //sauvegarde
        try {
            $this->getDoctrine()->getManager()->flush();
        } catch (\Throwable $th) {
            $message = "L'utilisateur <{$requestEditUserPassword['email']}> n'a pas pu être modifié. Veuillez contacter l'administrateur.";
            return $this->json(['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return $this->json(['code' => 200, 'message' => 'updated'], Response::HTTP_OK);
    }

    /**
     * *Suppression d'un utilisateur
     * 
     * @Route("/users/{id}/delete/", name="_delete", methods={"DELETE"}, requirements={"id"="\d+"})
     * @param UserPasswordHasherInterface $passwordHasher
     * @param FileUploader $fileUploader
     * @param User $user
     * @param Resquest $request
     * @return Response
     */
    public function delete(Request $request, User $user): Response
    {
        $requestUserDataDelete = json_decode($request->getContent(), true);
        if(isset($requestUserDataDelete['_ne_rien_ajouter_']) && $requestUserDataDelete['_ne_rien_ajouter_'] != null) {
            return $this->json(['code' => 400, 'message' => 'Qui êtes-vous?'], Response::HTTP_BAD_REQUEST);
        }
        //caractéristiques de l'utilisateur connecté
        $userStatus = $this->userStatus($user);
        if(!$this->isGranted('ROLE_SUPER_ADMIN')) { 
            if(!$userStatus['isCurrentUser']) { 
                $message = 'L\'utilisateur connecté n\'est pas le propriétaire du compte à supprimer.';
                return $this->json(['code' => 401, 'message' => $message], Response::HTTP_UNAUTHORIZED);
            }
        }
        $errors = [];
        //vérification du mot de passe
        $checkPassword = $this->passwordHasher->isPasswordValid($this->getUser(), $requestUserDataDelete['checkPassword']);
        if (!$checkPassword) { $errors['password'] = 'Mot de passe incorrect.'; }
        if (count($errors) > 0 ) {
            return $this->json(['code' => 400, 'message' => $errors], Response::HTTP_BAD_REQUEST);
        }

        //liste des noms des fichiers physiques à supprimer
        $fileList = [];
        $fileList[] = $user->getCover();
        $fileList[] = $user->getAvatar();
        foreach ($user->getTravels() as $travel) {
            $fileList[] = $travel->getCover();
            foreach ($travel->getSteps() as $step) { $fileList[] = $step->getCover(); }
            //* remplace la ligne au dessus
            //!foreach ($travel->getSteps() as $step) { 
            //!    $fileList[] = $step->getCover(); 
            //!    foreach ($step->getImages() as $image) {
            //!        $fileList[] = $image->getPath(); 
            //!    }
            //!}
        }

        //sauvegarde
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        } catch (\Throwable $th) {
            $message = "L'utilisateur <{$user->getEmail()}> n'a pas pu être supprimé. Veuillez contacter l'administrateur.";
            return $this->json(['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        //suppression des fichiers physiques
        foreach ($fileList as $file) { 
            if($file != null) { 
                try{ $this->fileUploader->deleteFile($file); }
                catch(\Throwable $th) { 
                    //$fileListToRemoveManually['user'] = $userId; 
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
     * @param User $user
     * @return array
     */
    private function userStatus($user): Array
    {
        $userStatus = [];

        //vérification si l'utilisateur connecté est bien le propriétaire du compte à modifier
        $currentUserId = $this->getUser()->getId();
        $userId = $user->getId();
        $currentUserId === $userId ? $userStatus['isCurrentUser'] = true : $userStatus['isCurrentUser'] = false;
        $this->isGranted('ROLE_ADMIN') ? $userStatus['hasAdminAccess'] = true : $userStatus['hasAdminAccess'] = false;

        return $userStatus;
    }
}
