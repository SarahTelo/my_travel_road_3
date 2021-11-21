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
use App\Service\FileUploader;

/**
* @Route("/api", name="users")
*/
class UserController extends AbstractController
{
    private $passwordHasher;
    private $fileUploader;
    private $validator;
    private $objectNormalizer;

    public function __construct( 
        UserPasswordHasherInterface $passwordHasher, 
        FileUploader $fileUploader,
        ValidatorInterface $validator,
        ObjectNormalizer $objectNormalizer)
    {
        $this->passwordHasher = $passwordHasher;
        $this->objectNormalizer = $objectNormalizer;
        $this->fileUploader = $fileUploader;
        $this->validator = $validator;
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
            //$users = $repository->findAll();
            $groups = 'user_list_admin';
        } else {
            //$users = $repository->findBy(['id' => $this->getUser()->getId()]);
            $groups = 'user_list';
        }
        $users = $repository->findAll();
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
        //données de la requête
        $requestUserDataNew = $request->request->All();
        unset($requestUserDataNew['cover']);
        unset($requestUserDataNew['avatar']);
        if(isset($requestUserDataNew['country'])) {
            $country = $requestUserDataNew['country'];
            unset($requestUserDataNew['country']);
        }

        //l'utilisateur est créé ici
        $user = $this->objectNormalizer->denormalize($requestUserDataNew, User::class);
        //vérification des erreurs
        $fileCover = $request->files->get('cover');
        $fileAvatar = $request->files->get('avatar');
        $errors = $this->validationForm($user, 'constraints_new', $fileCover, $fileAvatar);
        if (count($errors) > 0 ) {
            return $this->json(['code' => 400, 'message' => $errors], Response::HTTP_BAD_REQUEST);
        }

        //upload du fichier vers le dossier cible et récupération de son nom
        if (isset($fileCover)) { $user->setCover($this->fileUploader->upload($fileCover)); }
        if (isset($fileAvatar)) { $user->setAvatar($this->fileUploader->upload($fileAvatar)); }
        //encodage du mot de passe
        $user->setPassword($this->passwordHasher->hashPassword($user, $requestUserDataNew['password']));
        //vérification du rôle car l'utilisateur pourrait envoyer la clé "roles" dans le tableau de données reçues dans la $request
        $this->isGranted('ROLE_SUPER_ADMIN') ? $user->setRoles($requestUserDataNew['roles']) : $user->setRoles(['ROLE_USER']);
        //ajout du pays
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
        
        return $this->json(['code' => 201, 'message' => 'created'], Response::HTTP_CREATED);
    }

    /**
     * *Modification d'un utilisateur
     * 
     * @Route("/users/{id}/edit/", name="_edit", methods={"POST"}, requirements={"id"="\d+"})
     * @param ObjectNormalizer $objectNormalizer
     * @param UserPasswordHasherInterface $passwordHasher
     * @param FileUploader $fileUploader
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function edit(Request $request, User $user): Response
    {
        $requestUserDataEdit = $request->request->All();
        unset($requestUserDataEdit['cover']);
        unset($requestUserDataEdit['avatar']);
        unset($requestUserDataEdit['password']);

        //caractéristiques de l'utilisateur connecté
        $userStatus = $this->userStatus($user);
        if(!$userStatus['hasAdminAccess']) { 
            unset($requestUserDataEdit['roles']);
            if(!$userStatus['isCurrentUser']) { 
                $message = 'L\'utilisateur connecté n\'est pas le propriétaire du compte à modifier';
                return $this->json(['code' => 401, 'message' => $message], Response::HTTP_UNAUTHORIZED);
            }
        }

        //vérification des erreurs
        $fileCover = $request->files->get('cover');
        $fileAvatar = $request->files->get('avatar');
        $form = $this->createForm(UserType::class, $user);
        $form->submit($requestUserDataEdit, false);
        $errors = $this->validationForm($form->getNormData(), 'constraints_edit', $fileCover, $fileAvatar);
        //vérification du mot de passe après la création du tableau d'erreurs
        $checkPassword = $this->passwordHasher->isPasswordValid($this->getUser(), $requestUserDataEdit['checkPassword']);
        if (!$checkPassword) { $errors[] = 'Mot de passe incorrect'; }
        if(count($errors) === 0 && $form->isValid()) {
            //l'objet initial récupère les nouvelles données
            $user = $form->getData();
        } else {
            //erreurs liées au formulaire en lui même (extra_fields par exemple)
            $formErrors = $form->getErrors();
            foreach ($formErrors as $value) { $errors[] = $value->getMessage(); }
            return $this->json(['code' => 400, 'message' => $errors], Response::HTTP_BAD_REQUEST);
        }

        //gestion des images
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
        } else {
            $user->setCountry(null);
        }

        try {
            $this->getDoctrine()->getManager()->flush();
        } catch (\Throwable $th) {
            $message = "L'utilisateur <{$requestUserDataEdit['email']}> n'a pas pu être modifié. Veuillez contacter l'administrateur.";
            return $this->json(['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }
        return $this->json(['code' => 200, 'message' => 'updated'], Response::HTTP_OK);
    }

    /**
     * *Modification du mot de passe de l'utilisateur
     * 
     * @Route("/users/{id}/edit/password/", name="_edit_password", methods={"POST"}, requirements={"id"="\d+"})
     * @param UserPasswordHasherInterface $passwordHasher
     * @param FileUploader $fileUploader
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function editPassword(Request $request, User $user): Response
    {
        $requestEditUserPassword = $request->request->All();
        //caractéristiques de l'utilisateur connecté
        $userStatus = $this->userStatus($user);
        if(!$userStatus['isCurrentUser']) { 
            $message = 'L\'utilisateur connecté n\'est pas le propriétaire du compte à modifier';
            return $this->json(['code' => 401, 'message' => $message], Response::HTTP_UNAUTHORIZED);
        }

        //vérification des erreurs
        $errors = $this->validationForm($requestEditUserPassword, 'constraints_edit_password', null, null);
        $checkPassword = $this->passwordHasher->isPasswordValid($this->getUser(), $requestEditUserPassword['oldPassword']);
        if (!$checkPassword) { $errors[] = 'Ancien mot de passe incorrect'; }
        if (count($errors) > 0 ) {
            return $this->json(['code' => 400, 'message' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $requestEditUserPassword['password']));

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
     * @param User $user
     * @param UserPasswordHasherInterface $passwordHasher
     * @param FileUploader $fileUploader
     * @param Resquest $request
     * @param User $user
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
                $message = 'L\'utilisateur connecté n\'est pas le propriétaire du compte à supprimer';
                return $this->json(['code' => 401, 'message' => $message], Response::HTTP_UNAUTHORIZED);
            }
        }
        $errors = [];
        //vérification du mot de passe
        $checkPassword = $this->passwordHasher->isPasswordValid($this->getUser(), $requestUserDataDelete['checkPassword']);
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
                $message = 'deleted';
                $statusCode = Response::HTTP_OK; 
            } catch (\Throwable $th) {
                $message = "L'utilisateur <{$mail}> n'a pas pu être supprimé. Veuillez contacter l'administrateur.";
                $statusCode = Response::HTTP_SERVICE_UNAVAILABLE;
            }
        } else {
            $message = "L'utilisateur <{$mail}> n'a pas été supprimé car les fichiers physiques '$userCover' ou '$userAvatar' existent toujours.Veuillez contacter l'administrateur.";
            $statusCode = Response::HTTP_SERVICE_UNAVAILABLE;
        }
        return $this->json(['code' => $statusCode, 'message' => $message], $statusCode);
    }

    /**
     * *Tableau des autorisations de l'utilisateur actuel
     *
     * @param Entity $user
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

    /**
     * *Vérification des contraintes pour les champs du formulaire
     *
     * @param User $user
     * @param string $constraints
     * @param File $fileCover = null
     * @param File $fileAvatar = null
     * @param ObjectNormalizer $objectNormalizer
     * @param ValidatorInterface $validator
     * @return Array
     */
    private function validationForm($user, string $constraints, $fileCover = null, $fileAvatar = null): Array
    {
        //vérifications des contraintes (assert et manuelles)
        $errors = [];
        //files
        if(isset($fileCover)) {
            $brutErrors = $this->fileUploader->imageContraints($fileCover);
            foreach ($brutErrors as $value) { $errors[] = $value->getMessage(); }
        }
        if(isset($fileAvatar)) {
            $brutErrors = $this->fileUploader->imageContraints($fileAvatar);
            foreach ($brutErrors as $value) { $errors[] = $value->getMessage(); }
        }
        //data
        $brutErrors = $this->validator->validate($user, null, $constraints);
        foreach ($brutErrors as $value) { $errors[] = $value->getMessage(); }
        //dd($errors);
        return $errors;
    }

}
