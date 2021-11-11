<?php

namespace App\Controller;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Service\AntiSpamService;

/**
* @Route("/api", name="categories")
*/
class CategoryController extends AbstractController
{
    private $validator;
    private $antiSpam;
    private $objectNormalizer;

    public function __construct( 
        ValidatorInterface $validator, 
        AntiSpamService $antiSpam,
        ObjectNormalizer $objectNormalizer)
    {
        $this->objectNormalizer = $objectNormalizer;
        $this->validator = $validator;
        $this->antiSpam = $antiSpam;
    }
    
    /**
     * *Liste de toutes les catégories
     * 
     * @Route("/categories/", name="_list", methods={"GET"})
     * @Route("/admin/categories/", name="_list_admin", methods={"GET"})
     * @return Response
     */
    public function list(): Response
    {
        /** @var CategoryRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Category::class);

        if ($this->isGranted('ROLE_ADMIN')) {
            $groups = 'category_list_admin';
            $categories = $repository->findAll();
        } else {
            $groups = 'category_list_public';
            $categories = $repository->findBy( [], ['name' => 'ASC'] );
        }
        return $this->json($categories, Response::HTTP_OK, [], ['groups' => $groups]);
    }

    /**
     * *Liste de tous les voyages de la catégorie
     * 
     * @Route("/category/{id}/detail/", name="_detail", methods={"GET"}, requirements={"id"="\d+"})
     * @param Category $category
     * @return Response
     */
    public function detail(Category $category): Response
    {
        /** @var CategoryRepository $repository **/
        $repository = $this->getDoctrine()->getRepository(Category::class);
        $travels = $repository->findVisibleTravelsByCategory($category->getId());
        empty($travels) ? $dataCategory[] = $category : $dataCategory = $travels;
        return $this->json($dataCategory, Response::HTTP_OK, [], ['groups' => 'category_travel_detail']);
    }

    /**
     * *Ajout d'une catégorie
     * 
     * @Route("/admin/category/new/", name="_new", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
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
        $requestCategoryNew = $request->request->All();

        //pot de miel
        if(isset($requestCategoryNew['_ne_rien_ajouter_']) && $this->antiSpam->antiSpam($requestCategoryNew['_ne_rien_ajouter_'])) {
            return $this->json(['Qui êtes-vous?'], Response::HTTP_BAD_REQUEST);
        }

        $errors = [];
        $category = $this->objectNormalizer->denormalize($requestCategoryNew, Category::class);
        $brutErrors = $this->validator->validate($category, null, 'constraints_new');
        foreach ($brutErrors as $value) { $errors[] = $value->getMessage(); }
        if (count($errors) > 0 ) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        //sauvegarde
        $em = $this->getDoctrine()->getManager();
        try {
            $em->persist($category);
            $em->flush();
        } catch (\Throwable $th) {
            $message[] = "La catégorie '{$requestCategoryNew['name']}' n'a pas pu être ajoutée. Veuillez contacter l'administrateur.";
            return $this->json( $message, Response::HTTP_SERVICE_UNAVAILABLE );
        }
        return $this->json(['created'], Response::HTTP_CREATED);
    }

    /**
     * *Modification d'une catégorie
     * 
     * @Route("/admin/category/{id}/edit/", name="_edit", methods={"POST"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param Category $category
     * @return Response
     */
    public function edit(Request $request, Category $category) : Response
    {
        $contentType = $request->headers->get('Content-Type');
        if (!str_contains($contentType, 'multipart/form-data')) {
            return $this->json(['Nécessite \'multipart/form-data\' dans le header'], Response::HTTP_BAD_REQUEST);
        }

        //données de la requête
        $requestCategoryEdit = $request->request->All();

        //pot de miel
        if(isset($requestCategoryEdit['_ne_rien_ajouter_']) && $this->antiSpam->antiSpam($requestCategoryEdit['_ne_rien_ajouter_'])) {
            return $this->json(['Qui êtes-vous?'], Response::HTTP_BAD_REQUEST);
        }

        $errors = [];
        $dataToCheck = $this->objectNormalizer->denormalize($requestCategoryEdit, Category::class);
        $brutErrors = $this->validator->validate($dataToCheck, null, 'constraints_edit');
        foreach ($brutErrors as $value) { $errors[] = $value->getMessage(); }
        if (count($errors) > 0 ) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        //ne sera jamais null (contrainte) vu qu'il n'y a qu'un champ dans l'entité 'category'
        $category->setName($requestCategoryEdit['name']);

        //sauvegarde
        try {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
        } catch (\Throwable $th) {
            $message[] = "La catégorie '{$requestCategoryEdit['name']}' n'a pas pu être modifiée. Veuillez contacter l'administrateur.";
            return $this->json( $message, Response::HTTP_SERVICE_UNAVAILABLE );
        }
        return $this->json(['updated'], Response::HTTP_OK);
    }

    /**
     * *Suppression d'une catégorie
     * 
     * @Route("/admin/category/{id}/delete/", name="_delete", methods={"DELETE"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     * @param Category $category
     * @return Response
     */
    public function delete(Category $category) : Response
    {
        $categoryName = $category->getName();
        if(count($category->getTravels()) !== 0) {
            $message[] = "La catégorie '{$categoryName}' n'a pas pu être supprimée car il reste des voyages qui lui sont liés.";
            return $this->json( $message, Response::HTTP_SERVICE_UNAVAILABLE );
        }

        //sauvegarde
         try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($category);
            $em->flush();
        } catch (\Throwable $th) {
            $message[] = "La catégorie '{$categoryName}' n'a pas pu être supprimée. Veuillez contacter l'administrateur.";
            return $this->json( $message, Response::HTTP_SERVICE_UNAVAILABLE );
        }
        return $this->json(['deleted'], Response::HTTP_OK);
    }
}
