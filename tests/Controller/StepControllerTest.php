<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Travel;
use App\Repository\TravelRepository;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Entity\Step;
use App\Repository\StepRepository;
use App\Tests\ReuseFunctions;

/**
 * php bin/phpunit --filter StepControllerTest
 */
class StepControllerTest extends WebTestCase
{
    use ReuseFunctions;

    //*SHOW*****************************************

    /**
     * Affichage d'une étape: currentUser !== ownerTravel
     * php bin/console doctrine:fixtures:load --append --group=TravelFixtures
     *
     * @return void
     */
    public function testFailedStepTravelVisibilityFalse(): void
    {
        //php bin/phpunit --filter testFailedStepTravelVisibilityFalse
        $client = static::createClient();
        $allTravels = static::getContainer()->get(TravelRepository::class)->findBy(['visibility' => false]);
        //vérification qu'un voyage ait au moins une étape
        for ($i=0; $i < count($allTravels); $i++) { 
            $numberOfSteps = count($allTravels[$i]->getSteps());
            if($numberOfSteps > 0) {
                $travel = $allTravels[$i];
                break;
            }
        }
        if (!isset($travel)) { dd('Il n\'y a pas de voyages ayant au moins 1 étape!'); }
        foreach ($travel->getSteps() as $oneStep) { $step = $oneStep; }
        $currentUser = static::getContainer()->get(UserRepository::class)->findOneBy([], ['id' => 'ASC']);
        $client->loginUser($currentUser);
        $client->request('GET', '/api/travel/step/'.$step->getId().'/detail/');
        if($currentUser->getId() === $step->getTravel()->getUser()->getId()) {
            $this->assertResponseStatusCodeSame( 400, 'Le test doit vérifier que "currentUser !== ownerTravel"; mais le voyage sélectionné au random provoque le "currentUser(admin) === ownerTravel".'); //HTTP_BAD_REQUEST
        } else {
            $this->assertResponseStatusCodeSame(403, 'Mauvais code erreur.'); //HTTP_FORBIDDEN
        }
    }

    //*NEW******************************************

    /**
     * Ajouter une étape: currentUser === ownerTravel
     *
     * @return void
     */
    public function testSuccessfullStepNew() : void
    {
        //php bin/phpunit --filter testSuccessfullStepNew
        $client = static::createClient([], [
            'HTTP_CONTENT_TYPE' => 'multipart/form-data', 'CONTENT_TYPE' => 'multipart/form-data'
        ]);
        //ça pourrait être n'importe quel voyage au lieu de sélectionner un voyage qui a déjà une étape
        $step = static::getContainer()->get(StepRepository::class)->findOneBy([], ['id' => 'ASC']);
        $travel = $step->getTravel();
        $currentUser = $travel->getUser();
        $client->loginUser($currentUser);
        $client->request(
            'POST',
            '/api/travel/'.$travel->getId().'/step/new/',
            [
                "title" => "step 1",
                "_ne_rien_ajouter_" => "",
            ]
        );
        $this->assertResponseIsSuccessful();
    }

    /**
     * Ajouter une étape: currentUser !== ownerTravel
     *
     * @return void
     */
    public function testFailedStepNew() : void
    {
        //php bin/phpunit --filter testFailedStepNew
        $client = static::createClient([], [
            'HTTP_CONTENT_TYPE' => 'multipart/form-data', 'CONTENT_TYPE' => 'multipart/form-data'
        ]);

        $step = static::getContainer()->get(StepRepository::class)->findOneBy([], ['id' => 'ASC']);
        $travel = $step->getTravel();
        $ownerTravel = $travel->getUser();
        $currentUser = static::getContainer()->get(UserRepository::class)->findOneBy([], ['id' => 'DESC']);
        $client->loginUser($currentUser);
        $client->request(
            'POST',
            '/api/travel/'.$travel->getId().'/step/new/',
            [
                "title" => "step 1",
                "_ne_rien_ajouter_" => "",
            ]
        );

        if($currentUser->getId() === $ownerTravel->getId()) {
            $this->assertResponseStatusCodeSame( 400, 'Le test doit vérifier que "currentUser !== ownerTravel"; mais le voyage sélectionné au random provoque le "currentUser(admin) === ownerTravel".'); //HTTP_BAD_REQUEST
        } else {
            $this->assertResponseStatusCodeSame(401, 'Mauvais code erreur.'); //HTTP_UNAUTHORIZED
        }
    }

    /**
     * Ajouter une étape: currentUser(admin) !== ownerTravel
     *
     * @return void
     */
    public function testSuccessfulAdminStepNew() : void
    {
        //php bin/phpunit --filter testSuccessfulAdminStepNew
        $client = static::createClient([], [
            'HTTP_CONTENT_TYPE' => 'multipart/form-data', 'CONTENT_TYPE' => 'multipart/form-data'
        ]);

        $step = static::getContainer()->get(StepRepository::class)->findOneBy([], ['id' => 'ASC']);
        $travel = $step->getTravel();
        $ownerTravel = $travel->getUser();
        $currentUser = $this->getUserByEmail('admin@admin.com');
        $client->loginUser($currentUser);
        $client->request(
            'POST',
            '/api/travel/'.$travel->getId().'/step/new/',
            [
                "title" => "step 1",
                "_ne_rien_ajouter_" => "",
            ]
        );

        if($currentUser->getId() === $ownerTravel->getId()) {
            $this->assertResponseStatusCodeSame( 400, 'Le test doit vérifier que "currentUser(admin) !== ownerTravel"; mais le voyage sélectionné au random provoque le "currentUser === ownerTravel". L\'étape a été ajoutée.'); //HTTP_BAD_REQUEST
        } else {
            $this->assertResponseIsSuccessful();
        }
    }

    //*EDIT*****************************************

    /**
     * Modifier une étape: currentUser === ownerTravel
     *
     * @return void
     */
    public function testSuccessfulStepEdit() : void
    {
        //php bin/phpunit --filter testSuccessfulStepEdit
        $client = static::createClient([], [
            'HTTP_CONTENT_TYPE' => 'multipart/form-data', 'CONTENT_TYPE' => 'multipart/form-data'
        ]);
        $step = static::getContainer()->get(StepRepository::class)->findOneBy([], ['id' => 'ASC']);
        $travel = $step->getTravel();
        $currentUser = $travel->getUser();
        $client->loginUser($currentUser);
        $client->request(
            'POST', 
            '/api/travel/step/'.$step->getId().'/edit/',
            [
                "description" => "nouvelle description",
                "_ne_rien_ajouter_" => "",
            ]
        );
        $this->assertResponseIsSuccessful();
    }

    /**
     * Modifier une étape: currentUser !== ownerTravel
     *
     * @return void
     */
    public function testFailedStepEdit() : void
    {
        //php bin/phpunit --filter testFailedStepEdit
        $client = static::createClient([], [
            'HTTP_CONTENT_TYPE' => 'multipart/form-data', 'CONTENT_TYPE' => 'multipart/form-data'
        ]);
        $step = static::getContainer()->get(StepRepository::class)->findOneBy([], ['id' => 'ASC']);
        $travel = $step->getTravel();
        $ownerTravel = $travel->getUser();
        $currentUser = static::getContainer()->get(UserRepository::class)->findOneBy([], ['id' => 'DESC']);

        $client->loginUser($currentUser);
        $client->request(
            'POST', 
            '/api/travels/'.$travel->getId().'/edit/',
            [
                "description" => "changement de description",
                "_ne_rien_ajouter_" => "",
            ]
        );
        if($currentUser->getId() === $ownerTravel->getId()) {
            $this->assertResponseStatusCodeSame( 400, 'Le test doit vérifier que "currentUser !== ownerTravel"; mais le voyage sélectionné au random provoque le "currentUser(admin) === ownerTravel".'); //HTTP_BAD_REQUEST
        } else {
            $this->assertResponseStatusCodeSame(401, 'Mauvais code erreur.'); //HTTP_UNAUTHORIZED
        }
    }

    /**
     * Modifier une étape: currentUser(admin) !== ownerTravel
     *
     * @return void
     */
    public function testSuccessfulAdminStepEdit() : void
    {
        //php bin/phpunit --filter testSuccessfulAdminStepEdit
        $client = static::createClient([], [
            'HTTP_CONTENT_TYPE' => 'multipart/form-data', 'CONTENT_TYPE' => 'multipart/form-data'
        ]);
        $step = static::getContainer()->get(StepRepository::class)->findOneBy([], ['id' => 'ASC']);
        $ownerTravel = $step->getTravel();
        $currentUser = $this->getUserByEmail('admin@admin.com');
        $client->loginUser($currentUser);
        $client->request(
            'POST', 
            '/api/travel/step/'.$step->getId().'/edit/',
            [
                "description" => "nouvelle description par l'admin",
                "_ne_rien_ajouter_" => "",
            ]
        );
        if($currentUser->getId() === $ownerTravel->getId()) {
            $this->assertResponseStatusCodeSame( 400, 'Le test doit vérifier que "currentUser(admin) !== ownerTravel"; mais le voyage sélectionné au random provoque le "currentUser === ownerTravel". L\'étape a quand même été modifiée.'); //HTTP_BAD_REQUEST
        } else {
            $this->assertResponseIsSuccessful();
        }
    }

    //*DELETE***************************************

    /**
     * Supprimer une étape: currentUser !== ownerTravel
     *
     * @return void
     */
    public function testFailedStepDelete() : void
    {
        //php bin/phpunit --filter testFailedStepDelete
        $client = static::createClient();

        $step = static::getContainer()->get(StepRepository::class)->findOneBy([], ['id' => 'ASC']);
        $travel = $step->getTravel();
        $ownerTravel = $travel->getUser();
        $currentUser = static::getContainer()->get(UserRepository::class)->findOneBy([], ['id' => 'DESC']);

        $client->loginUser($currentUser);
        $client->request(
            'DELETE', 
            '/api/travel/step/'.$step->getId().'/delete/',
            [],
            [],
            [],
            '{ "password" : "user", "_ne_rien_ajouter_" : "" }'
        );

        if($currentUser->getId() === $ownerTravel->getId()) {
            $this->assertResponseStatusCodeSame( 400, 'Le test doit vérifier que "currentUser !== ownerTravel"; mais le voyage sélectionné au random provoque le "currentUser(admin) === ownerTravel".'); //HTTP_BAD_REQUEST
        } else {
            $this->assertResponseStatusCodeSame(401, 'Mauvais code erreur.'); //HTTP_UNAUTHORIZED
        }
    }

    /**
     * Supprimer une étape: currentUser === ownerTravel
     *
     * @return void
     */
    public function testSuccessfulStepDelete() : void
    {
        //php bin/phpunit --filter testSuccessfulStepDelete
        $client = static::createClient();
        $step = static::getContainer()->get(StepRepository::class)->findOneBy([], ['id' => 'ASC']);
        $travel = $step->getTravel();
        $currentUser = $travel->getUser();
        $client->loginUser($currentUser);
        $client->request('DELETE', '/api/travel/step/'.$step->getId().'/delete/');
        $this->assertResponseIsSuccessful();
    }

    /**
     * Supprimer une étape: currentUser(admin) !== ownerTravel
     *
     * @return void
     */
    public function testSuccessfulAdminStepDelete() : void
    {
        //php bin/phpunit --filter testSuccessfulAdminStepDelete
        $client = static::createClient();
        $step = static::getContainer()->get(StepRepository::class)->findOneBy([], ['id' => 'ASC']);
        $ownerTravel = $step->getTravel()->getUser();
        $currentUser = $this->getUserByEmail('admin@admin.com');

        $client->loginUser($currentUser);
        $client->request('DELETE', '/api/travel/step/'.$step->getId().'/delete/');
        if($currentUser->getId() === $ownerTravel->getId()) {
            $this->assertResponseStatusCodeSame( 400, 'Le test doit vérifier que "currentUser(admin) !== ownerTravel"; mais le voyage sélectionné au random provoque le "currentUser === ownerTravel". L\'étape a quand même été supprimée.'); //HTTP_BAD_REQUEST
        } else {
            $this->assertResponseIsSuccessful();
        }
    }
}