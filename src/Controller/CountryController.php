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

//todo: utiliser une api pour récupérer le nom des pays et leurs coordonnées (capitale)

/**
* @Route("/api", name="countries")
*/
class CountryController extends AbstractController
{
    private $validator;
    private $antiSpam;
    private $objectNormalizer;

    public function __construct( 
        ValidatorInterface $validator, 
        ObjectNormalizer $objectNormalizer)
    {
        $this->objectNormalizer = $objectNormalizer;
        $this->validator = $validator;
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
        return $this->json($countries, Response::HTTP_OK, [], ['groups' => $groups]);
    }

    /**
     * *Liste de tous les utilisateurs venants d'un pays
     * 
     * @Route("/country/{id}/detail/", name="_detail", methods={"GET"}, requirements={"id"="\d+"})
     * @param Country $country
     * @return Response
     */
    public function detail(Country $country): Response
    {
        return $this->json($country, Response::HTTP_OK, [], ['groups' => 'country_user_detail']);
    }

    /**
     * *Ajout d'un pays
     * 
     * @Route("/admin/country/new/", name="_new", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @return Response
     */
    public function new(Request $request) : Response
    {
        //données de la requête
        $requestCountryNew = $request->request->All();
        //création de l'objet et vérification de ses contraintes
        $errors = [];
        $country = $this->objectNormalizer->denormalize($requestCountryNew, Country::class);
        $brutErrors = $this->validator->validate($country, null, 'constraints_new');
        foreach ($brutErrors as $value) { $errors[] = $value->getMessage(); }
        if (count($errors) > 0 ) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        //sauvegarde
        $em = $this->getDoctrine()->getManager();
        try {
            $em->persist($country);
            $em->flush();
        } catch (\Throwable $th) {
            $message[] = "Le pays '{$requestCountryNew['name']}' n'a pas pu être ajouté. Veuillez contacter l'administrateur.";
            return $this->json( $message, Response::HTTP_SERVICE_UNAVAILABLE );
        }
        return $this->json(['created'], Response::HTTP_CREATED);
    }

    /**
     * *Modification d'un pays
     * 
     * @Route("/admin/country/{id}/edit/", name="_edit", methods={"POST"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param Country $country
     * @return Response
     */
    public function edit(Request $request, Country $country) : Response
    {
        //données de la requête
        $requestCountryEdit = $request->request->All();
        //création d'un formulaire avec les anciennes données et soumission des nouvelles
        $form = $this->createForm(CountryType::class, $country);
        $form->submit($requestCountryEdit, false);
        
        $countryErrors = $this->validator->validate($form->getNormData(), null, ['constraints_edit']);
        foreach ($countryErrors as $value) { $errors[] = $value->getMessage(); }
        if(count($countryErrors) === 0 && $form->isValid()) {
            //l'objet initial récupère les nouvelles données
            $country = $form->getData();
        } else {
            $formErrors = $form->getErrors();
            foreach ($formErrors as $value) { $errors[] = $value->getMessage(); }
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        //sauvegarde
        try {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
        } catch (\Throwable $th) {
            $message[] = "Le pays '{$requestCountryEdit['name']}' n'a pas pu être modifié. Veuillez contacter l'administrateur.";
            return $this->json( $message, Response::HTTP_SERVICE_UNAVAILABLE );
        }
        return $this->json(['updated'], Response::HTTP_OK);
    }
    
    /**
     * *Suppression d'un pays
     * 
     * @Route("/admin/country/{id}/delete/", name="_delete", methods={"DELETE"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     * @param Country $country
     * @return Response
     */
    public function delete(Country $country) : Response
    {
        $countryName = $country->getName();
        if(count($country->getUsers()) !== 0) {
            $message[] = "Le pays '{$countryName}' n'a pas pu être supprimé car il reste des utilisateurs qui lui sont liés.";
            return $this->json( $message, Response::HTTP_SERVICE_UNAVAILABLE );
        }

        //sauvegarde
         try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($country);
            $em->flush();
        } catch (\Throwable $th) {
            $message[] = "Le pays '{$countryName}' n'a pas pu être supprimé. Veuillez contacter l'administrateur.";
            return $this->json( $message, Response::HTTP_SERVICE_UNAVAILABLE );
        }
        return $this->json(['deleted'], Response::HTTP_OK);
    }
}
