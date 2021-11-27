<?php

namespace App\Controller;

use App\Entity\Country;
use App\Form\CountryType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Service\ProcessFormService;

//todo: utiliser une api pour récupérer le nom des pays et leurs coordonnées (capitale)

/**
* @Route("/api", name="countries")
*/
class CountryController extends AbstractController
{
    private $validator;
    private $objectNormalizer;
    private $processForm;

    public function __construct( 
        ValidatorInterface $validator, 
        ObjectNormalizer $objectNormalizer, 
        ProcessFormService $processForm)
    {
        $this->objectNormalizer = $objectNormalizer;
        $this->validator = $validator;
        $this->processForm = $processForm;
    }

    /**
     * *Liste de tous les pays
     * 
     * @Route("/countries/", name="_list", methods={"GET"})
     * @Route("/admin/countries/", name="_list_admin", methods={"GET"})
     * @return Response
     */
    public function list(): Response
    {
        /** @var CountryRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Country::class);

        if ($this->isGranted('ROLE_ADMIN')) {
            $groups = 'country_list_admin';
            $countries = $repository->findAll();
        } else {
            $groups = 'country_list_public';
            $countries = $repository->findBy( [], ['name' => 'ASC'] );
        }
        return $this->json(['countryList' => $countries], Response::HTTP_OK, [], ['groups' => $groups]);
    }

    /**
     * *Liste de tous les utilisateurs venants d'un pays
     * 
     * @Route("/country/{id}/detail/", name="_detail", methods={"GET"}, requirements={"id"="\d+"})
     * @Route("/admin/country/{id}/detail/", name="_detail_admin", methods={"GET"}, requirements={"id"="\d+"})
     * @param Country $country
     * @return Response
     */
    public function detail(Country $country): Response
    {
        $this->isGranted('ROLE_ADMIN') ? $groups = 'country_user_detail_admin' : $groups = 'country_user_detail';
        return $this->json($country, Response::HTTP_OK, [], ['groups' => $groups]);
    }

    /**
     * *Ajout d'un pays
     * 
     * @Route("/admin/country/new/", name="_new", methods={"POST"})
     * @param ValidatorInterface $validator
     * @param ObjectNormalizer $objectNormalizer
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $requestCountryNew = $request->request->All();
        //création de l'objet et vérification de ses contraintes
        $errors = [];
        $country = $this->objectNormalizer->denormalize($requestCountryNew, Country::class);
        $brutErrors = $this->validator->validate($country, null, 'constraints_new');
        foreach ($brutErrors as $value) { $errors[$value->getPropertyPath()] = $value->getMessage(); }
        if (count($errors) > 0 ) {
            return $this->json(['code' => 400, 'message' => $errors], Response::HTTP_BAD_REQUEST);
        }

        //sauvegarde
        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($country);
            $em->flush();
        } catch (\Throwable $th) {
            $message = "Le pays '{$requestCountryNew['name']}' n'a pas pu être ajouté. Veuillez contacter l'administrateur.";
            return $this->json(['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }
        return $this->json(['code' => 201, 'message' => ['country_id' => $country->getId()]], Response::HTTP_CREATED);
    }

    /**
     * *Modification d'un pays
     * 
     * @Route("/admin/country/{id}/edit/", name="_edit", methods={"POST"}, requirements={"id"="\d+"})
     * @param Country $country
     * @param ProcessFormService $processForm
     * @param Request $request
     * @return Response
     */
    public function edit(Request $request, Country $country): Response
    {
        $requestCountryEdit = $request->request->All();
        $oldCountryName = $country->getName();
        //création d'un formulaire avec les anciennes données et vérification des contraintes
        $form = $this->createForm(CountryType::class, $country);
        $entity = $this->processForm->validationFormEdit($form, $requestCountryEdit);
        if(empty($entity['errors'])) {
            $country = $form->getData();
        } else {
            return $this->json(['code' => 400, 'message' => $entity['errors']], Response::HTTP_BAD_REQUEST);
        }

        //sauvegarde
        try {
            $this->getDoctrine()->getManager()->flush();
        } catch (\Throwable $th) {
            $message = "Le pays '{$oldCountryName}' n'a pas pu être modifié. Veuillez contacter l'administrateur.";
            return $this->json(['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }
        return $this->json(['code' => 200, 'message' => 'updated'], Response::HTTP_OK);
    }
    
    /**
     * *Suppression d'un pays
     * 
     * @Route("/admin/country/{id}/delete/", name="_delete", methods={"DELETE"}, requirements={"id"="\d+"})
     * @param Country $country
     * @return Response
     */
    public function delete(Country $country): Response
    {
        if(count($country->getUsers()) !== 0) {
            $message = "Le pays '{$country->getName()}' n'a pas pu être supprimé car il reste des utilisateurs qui lui sont liés.";
            return $this->json(['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        //sauvegarde
         try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($country);
            $em->flush();
        } catch (\Throwable $th) {
            $message = "Le pays '{$country->getName()}' n'a pas pu être supprimé. Veuillez contacter l'administrateur.";
            return $this->json( ['code' => 503, 'message' => $message], Response::HTTP_SERVICE_UNAVAILABLE );
        }
        return $this->json(['code' => 200, 'message' => 'deleted'], Response::HTTP_OK);
    }
}
