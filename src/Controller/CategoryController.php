<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\ProcessFormService;

/**
* @Route("/api", name="categories")
*/
class CategoryController extends AbstractController
{
    private $processForm;

    public function __construct(ProcessFormService $processForm)
    {
        $this->processForm = $processForm;
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
        return $this->json(['categoryList' => $categories], Response::HTTP_OK, [], ['groups' => $groups]);
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
        return $this->json(['categroyDetail' => $dataCategory], Response::HTTP_OK, [], ['groups' => 'category_travel_detail']);
    }

    /**
     * *Liste de tous les voyages de la catégorie ADMIN
     * 
     * @Route("/admin/category/{id}/detail/", name="_detail_admin", methods={"GET"}, requirements={"id"="\d+"})
     * @param Category $category
     * @return Response
     */
    public function detailAdmin(Category $category): Response
    {
        return $this->json(['categroyDetail' => $category], Response::HTTP_OK, [], ['groups' => 'category_travel_detail_admin']);
    }

    /**
     * *Ajout d'une catégorie
     * 
     * @Route("/admin/category/new/", name="_new", methods={"POST"})
     * @param ProcessFormService $processForm
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        //préparation des données
        $requestCategoryNew = $request->request->All();
        //formulaire
        $form = $this->createForm(CategoryType::class, null, ['validation_groups' => 'constraints_new']);
        $form->submit($requestCategoryNew, false);
        //vérification des contraintes
        $errors = [];
        if (!$form->isValid()) {
            $errors = $this->processForm->validationForm($form);
            if (!empty($errors)) {
                return $this->json(['code' => 400, 'message' => $errors], Response::HTTP_BAD_REQUEST);
            }
        }
        //création de la catégorie
        $category = $form->getData();
        //sauvegarde
        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();
        } catch (\Throwable $th) {
            $message = "La catégorie '{$requestCategoryNew['name']}' n'a pas pu être ajoutée. Veuillez contacter l'administrateur.";
            return $this->json(['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }
        return $this->json(['code' => 201, 'message' => ['category_id' => $category->getId()]], Response::HTTP_CREATED);
    }

    /**
     * *Modification d'une catégorie
     * 
     * @Route("/admin/category/{id}/edit/", name="_edit", methods={"POST"}, requirements={"id"="\d+"})
     * @param ProcessFormService $processForm
     * @param Category $category
     * @param Request $request
     * @return Response
     */
    public function edit(Request $request, Category $category): Response
    {
        $oldCategoryName = $category->getName();
        //préparation des données
        $requestCategoryEdit = $request->request->All();
        //formulaire
        $form = $this->createForm(CategoryType::class, $category, ['validation_groups' => 'constraints_edit']);
        $form->submit($requestCategoryEdit, false);
        //vérification des contraintes
        $errors = [];
        if (!$form->isValid()) {
            $errors = $this->processForm->validationForm($form);
            if (!empty($errors)) {
                return $this->json(['code' => 400, 'message' => $errors], Response::HTTP_BAD_REQUEST);
            }
        }
        //sauvegarde
        try {
            $this->getDoctrine()->getManager()->flush();
        } catch (\Throwable $th) {
            $message = "La catégorie '{$oldCategoryName}' n'a pas pu être modifiée. Veuillez contacter l'administrateur.";
            return $this->json(['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }
        return $this->json(['code' => 200, 'message' => 'updated'], Response::HTTP_OK);
    }

    /**
     * *Suppression d'une catégorie
     * 
     * @Route("/admin/category/{id}/delete/", name="_delete", methods={"DELETE"}, requirements={"id"="\d+"})
     * @param Category $category
     * @return Response
     */
    public function delete(Category $category): Response
    {
        if(count($category->getTravels()) !== 0) {
            $message = "La catégorie '{$category->getName()}' n'a pas pu être supprimée car il reste des voyages qui lui sont liés.";
            return $this->json( ['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE );
        }

        //sauvegarde
         try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($category);
            $em->flush();
        } catch (\Throwable $th) {
            $message = "La catégorie '{$category->getName()}' n'a pas pu être supprimée. Veuillez contacter l'administrateur.";
            return $this->json( ['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE );
        }
        return $this->json(['code' => 200, 'message' => 'deleted'], Response::HTTP_OK);
    }
}
