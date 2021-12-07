<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Travel;
use App\Repository\TravelRepository;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\ReuseFunctions;

/**
 * User disponibles: user3, user4 et user5
 * php bin/phpunit --filter TravelControllerTest
 */
class TravelControllerTest extends WebTestCase
{
    use ReuseFunctions;

    //*SHOW*****************************************

    /**
     * Affichage d'un voyage: currentUser !== ownerTravel
     * php bin/console doctrine:fixtures:load --append --group=TravelFixtures
     *
     * @return void
     */
    public function testFailedTravelVisibilityFalse(): void
    {
        //php bin/phpunit --filter testFailedTravelVisibilityFalse
        $client = static::createClient();
        $travel = $this->getTravelByVisibilityFalse();
        $currentUser = static::getContainer()->get(UserRepository::class)->findOneBy([], ['id' => 'ASC']);
        $client->loginUser($currentUser);
        $client->request('GET', '/api/travels/'.$travel->getId().'/detail/');
        if($currentUser->getId() === $travel->getUser()->getId()) {
            $this->assertResponseStatusCodeSame( 400, 'Le test doit vérifier que "currentUser !== ownerTravel"; mais le voyage sélectionné au random provoque le "currentUser(admin) === ownerTravel". Le voyage a quand même été supprimé.'); //HTTP_BAD_REQUEST
        } else {
            $this->assertResponseStatusCodeSame(403, 'Mauvais code erreur.'); //HTTP_FORBIDDEN
        }
    }

    //*NEW******************************************

    /**
     * Ajouter un voyage: currentUser === ownerTravel
     *
     * @return void
     */
    public function testSuccessfullTravelNew() : void
    {
        //php bin/phpunit --filter testSuccessfullTravelNew
        $client = static::createClient([], [
            'HTTP_CONTENT_TYPE' => 'multipart/form-data', 'CONTENT_TYPE' => 'multipart/form-data'
        ]);
        $user = $this->getUserByEmail('user3@user.com');
        $client->loginUser($user);
        $client->request(
            'POST',
            '/api/travels/new/',
            [
                "title" => "travel 1",
                "status" => 0,
                "visibility" => 0,
                "_ne_rien_ajouter_" => "",
            ]
        );
        $this->assertResponseIsSuccessful();
    }

    //*EDIT*****************************************

    /**
     * Modifier un voyage: currentUser === ownerTravel
     * Attention, si test seul, il faut lancer une fois: php bin/phpunit --filter testSuccessfullTravelNew
     *
     * @return void
     */
    public function testSuccessfulTravelEdit() : void
    {
        //php bin/phpunit --filter testSuccessfulTravelEdit
        $client = static::createClient([], [
            'HTTP_CONTENT_TYPE' => 'multipart/form-data', 'CONTENT_TYPE' => 'multipart/form-data'
        ]);
        $currentUser = $this->getUserByEmail('user3@user.com');
        $travel = $this->getTravelByUser($currentUser->getId());
        $client->loginUser($currentUser);
        $client->request(
            'POST', 
            '/api/travels/'.$travel->getId().'/edit/',
            [
                "description" => "description travel 1",
                "_ne_rien_ajouter_" => "",
            ]
        );
        $this->assertResponseIsSuccessful();
    }

    /**
     * Modifier un voyage: currentUser !== ownerTravel
     * Attention, il faut lancer une fois: php bin/phpunit --filter testSuccessfullTravelNew
     *
     * @return void
     */
    public function testFailedTravelEdit() : void
    {
        //php bin/phpunit --filter testFailedTravelEdit
        $client = static::createClient([], [
            'HTTP_CONTENT_TYPE' => 'multipart/form-data', 'CONTENT_TYPE' => 'multipart/form-data'
        ]);

        $currentUser = $this->getUserByEmail('user4@user.com');
        $ownerTravelUser = $this->getUserByEmail('user3@user.com');
        $travel = $this->getTravelByUser($ownerTravelUser->getId());

        $client->loginUser($currentUser);
        $client->request(
            'POST', 
            '/api/travels/'.$travel->getId().'/edit/',
            [
                "description" => "description travel 1",
                "_ne_rien_ajouter_" => "",
            ]
        );
        $this->assertResponseStatusCodeSame(401, 'Mauvais code erreur.'); //HTTP_UNAUTHORIZED
    }

    /**
     * Modifier un voyage: currentUser(admin) !== ownerTravel
     * Attention, il faut lancer une fois: php bin/phpunit --filter testSuccessfullTravelNew
     *
     * @return void
     */
    public function testSuccessfulAdminTravelEdit() : void
    {
        //php bin/phpunit --filter testSuccessfulAdminTravelEdit
        $client = static::createClient([], [
            'HTTP_CONTENT_TYPE' => 'multipart/form-data', 'CONTENT_TYPE' => 'multipart/form-data'
        ]);

        $currentUser = $this->getUserByEmail('admin@admin.com');
        $ownerTravelUser = $this->getUserByEmail('user3@user.com');
        $travel = $this->getTravelByUser($ownerTravelUser->getId());

        $client->loginUser($currentUser);
        $client->request(
            'POST', 
            '/api/travels/'.$travel->getId().'/edit/',
            [
                "description" => "description changée par un admin",
                "_ne_rien_ajouter_" => "",
            ]
        );
        $this->assertResponseIsSuccessful();
    }

    //*DELETE***************************************

    /**
     * Supprimer un voyage: currentUser !== ownerTravel
     * Attention, il faut lancer une fois: php bin/phpunit --filter testSuccessfullTravelNew
     *
     * @return void
     */
    public function testFailedTravelDelete() : void
    {
        //php bin/phpunit --filter testFailedTravelDelete
        $client = static::createClient();

        $currentUser = $this->getUserByEmail('user4@user.com');
        $ownerTravelUser = $this->getUserByEmail('user3@user.com');
        $travel = $this->getTravelByUser($ownerTravelUser->getId());

        $client->loginUser($currentUser);
        $client->request(
            'DELETE', 
            '/api/travels/'.$travel->getId().'/delete/',
            [],
            [],
            [],
            '{ "password" : "user", "_ne_rien_ajouter_" : "" }'
        );
        $this->assertResponseStatusCodeSame(401, 'Mauvais code erreur.'); //HTTP_UNAUTHORIZED
    }

    /**
     * Supprimer un voyage: currentUser === ownerTravel
     * Attention, il faut lancer une fois: php bin/phpunit --filter testSuccessfullTravelNew
     *
     * @return void
     */
    public function testSuccessfulTravelDelete() : void
    {
        //php bin/phpunit --filter testSuccessfulTravelDelete
        $client = static::createClient();

        $currentUser = $this->getUserByEmail('user3@user.com');
        $travel = $this->getTravelByUser($currentUser->getId());

        $client->loginUser($currentUser);
        $client->request(
            'DELETE', 
            '/api/travels/'.$travel->getId().'/delete/',
            [],
            [],
            [],
            '{ "password" : "user", "_ne_rien_ajouter_" : "" }'
        );
        $this->assertResponseIsSuccessful();
    }

    /**
     * Supprimer un voyage: currentUser(admin) !== ownerTravel
     *
     * @return void
     */
    public function testSuccessfulAdminTravelDelete() : void
    {
        //php bin/phpunit --filter testSuccessfulAdminTravelDelete
        $client = static::createClient();
        $currentUser = $this->getUserByEmail('admin@admin.com');
        $client->loginUser($currentUser);
        $travel = static::getContainer()->get(TravelRepository::class)->findOneBy([], ['id' => 'ASC']);
        $client->request(
            'DELETE', 
            '/api/travels/'.$travel->getId().'/delete/',
            [],
            [],
            [],
            '{ "password" : "admin", "_ne_rien_ajouter_" : "" }'
        );

        if($currentUser->getId() === $travel->getUser()->getId()) {
            $this->assertResponseStatusCodeSame( 400, 'Le test doit vérifier que "currentUser(admin) !== ownerTravel"; mais le voyage sélectionné au random provoque le "currentUser === ownerTravel". Le voyage a quand même été supprimé.'); //HTTP_BAD_REQUEST
        } else {
            $this->assertResponseIsSuccessful();
        }
    }
}