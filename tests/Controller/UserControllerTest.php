<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests\ReuseFunctions;

/**
 * php bin/phpunit --filter UserControllerTest
 */
class UserControllerTest extends WebTestCase
{
    use ReuseFunctions;

    //*DATA PROVIDERS*******************************

    /**
     * Données pour créer des utilisateurs
     *
     * @return iterable
     */
    public function provideUserData(): iterable
    {
        yield 'user1' => [
            "route" => '/api/users/new/',
            "email" => "user1@user.com",
            "password" => "user",
            "pseudo" => "mon pseudo",
            "_ne_rien_ajouter_" => "",
        ];
        yield 'user2' => [
            "route" => '/api/users/new/',
            "email" => "user2@user.com",
            "password" => "user",
            "pseudo" => "mon pseudo",
            "_ne_rien_ajouter_" => "",
        ];
    }

    //*NEW******************************************

    /**
     * Ajouter un utilisateur
     *
     * @dataProvider provideUserData
     * @return void
     */
    public function testSuccessfullUsersNew($route, $email, $pass, $pseudo, $spam) : void
    {
        //php bin/phpunit --filter testSuccessfullUsersNew@user1
        //php bin/phpunit --filter "testSuccessfullUsersNew@.*user.*"
        $client = static::createClient([], [
            'HTTP_CONTENT_TYPE' => 'multipart/form-data', 'CONTENT_TYPE' => 'multipart/form-data'
        ]);
        $client->request(
            'POST',
            $route,
                [
                    "email" => $email,
                    "password" => $pass,
                    "pseudo" => $pseudo,
                    "_ne_rien_ajouter_" => $spam,
                ]
        );
        $this->assertResponseIsSuccessful();
    }

    /**
     * Ajouter un utilisateur avec un rôle super_admin
     *
     * @return void
     */
    public function testSuccessfullSuperAdminUserNew() : void
    {
        //php bin/phpunit --filter testSuccessfullSuperAdminUserNew
        $client = static::createClient([], [
            'HTTP_CONTENT_TYPE' => 'multipart/form-data', 'CONTENT_TYPE' => 'multipart/form-data'
        ]);
        $user = $this->getUserByEmail('super.admin@admin.com');
        $client->loginUser($user);
        $client->request(
            'POST', 
            '/api/users/new/',
            [
                "email" => "super.admin2@admin.com",
                "password" => "admin",
                "pseudo" => "super admin",
                "roles" => ['ROLE_SUPER_ADMIN'],
                "_ne_rien_ajouter_" => "",
            ]
        );
        $this->assertResponseIsSuccessful();
    }

    //*EDIT*****************************************

    /**
     * Modifier un utilisateur: currentUser === userToEdit
     *
     * @return void
     */
    public function testSuccessfullUserEdit() : void
    {
        //php bin/phpunit --filter testSuccessfullUserEdit
        $client = static::createClient([], [
            'HTTP_CONTENT_TYPE' => 'multipart/form-data', 'CONTENT_TYPE' => 'multipart/form-data'
        ]);
        $user = $this->getUserByEmail('user1@user.com');
        $client->loginUser($user);
        $client->request(
            'POST', 
            '/api/users/'.$user->getId().'/edit/',
            [
                "checkPassword" => "user",
                "pseudo" => "mon nouveau pseudo",
                "_ne_rien_ajouter_" => "",
            ]
        );
        $this->assertResponseIsSuccessful();
    }

    /**
     * Modifier un utilisateur: currentUser(admin) !== userToEdit
     *
     * @return void
     */
    public function testSuccessfullAdminUserEdit() : void
    {
        //php bin/phpunit --filter testSuccessfullAdminUserEdit
        $client = static::createClient([], [
            'HTTP_CONTENT_TYPE' => 'multipart/form-data', 'CONTENT_TYPE' => 'multipart/form-data'
        ]);
        $currentUser = $this->getUserByEmail('admin@admin.com');
        $client->loginUser($currentUser);
        $userToEdit = $this->getUserByEmail('user1@user.com');
        $client->request(
            'POST',
            '/api/users/'.$userToEdit->getId().'/edit/',
            [
                "checkPassword" => "admin",
                "pseudo" => "son nouveau pseudo",
                "_ne_rien_ajouter_" => "",
            ]
        );
        $this->assertResponseIsSuccessful();
    }

    /**
     * Modifier un utilisateur: currentUser !== userToEdit
     *
     * @return void
     */
    public function testFailedUserEdit() : void
    {
        //php bin/phpunit --filter testFailedUserEdit
        $client = static::createClient([], [
            'HTTP_CONTENT_TYPE' => 'multipart/form-data', 'CONTENT_TYPE' => 'multipart/form-data'
        ]);
        $currentUser = $this->getUserByEmail('user1@user.com');
        $client->loginUser($currentUser);
        $userToEdit = $this->getUserByEmail('admin@admin.com');
        $client->request(
            'POST', 
            '/api/users/'.$userToEdit->getId().'/edit/',
            [
                "checkPassword" => "user",
                "pseudo" => "mon nouveau pseudo",
                "_ne_rien_ajouter_" => "",
            ]
        );
        $this->assertResponseStatusCodeSame(401, 'Mauvais code erreur.');
    }

    //*EDIT_PASSWORD********************************

    /**
     * Modifier le mot de passe d'un utilisateur: currentUser === userToEditPassword
     *
     * @return void
     */
    public function testSuccessfullUserEditPassword() : void
    {
        //php bin/phpunit --filter testSuccessfullUserEditPassword
        $client = static::createClient([], [
            'HTTP_CONTENT_TYPE' => 'multipart/form-data', 'CONTENT_TYPE' => 'multipart/form-data'
        ]);
        $user = $this->getUserByEmail('user1@user.com');
        $client->loginUser($user);
        $client->request(
            'POST', 
            '/api/users/'.$user->getId().'/edit/password/',
            [
                "oldPassword" => "user",
                "password" => "user",
                "_ne_rien_ajouter_" => "",
            ]
        );
        $this->assertResponseIsSuccessful();
    }

    /**
     * Modifier le mot de passe d'un utilisateur: currentUser !== userToEditPassword
     *
     * @return void
     */
    public function testFaildeUserEditPassword() : void
    {
        //php bin/phpunit --filter testFaildeUserEditPassword
        $client = static::createClient([], [
            'HTTP_CONTENT_TYPE' => 'multipart/form-data', 'CONTENT_TYPE' => 'multipart/form-data'
        ]);
        $currentUser = $this->getUserByEmail('admin@admin.com');
        $client->loginUser($currentUser);
        $userToEdit = $this->getUserByEmail('user1@user.com');
        $client->request(
            'POST',
            '/api/users/'.$userToEdit->getId().'/edit/password/',
            [
                "oldPassword" => "admin",
                "password" => "admin",
                "_ne_rien_ajouter_" => "",
            ]
        );
        $this->assertResponseStatusCodeSame(401, 'Mauvais code erreur.');
    }

    //*DELETE***************************************

    /**
     * Supprimer un utilisateur: currentUser !== userToDelete
     *
     * @return void
     */
    public function testFailedUserDelete() : void
    {
        //php bin/phpunit --filter testFailedUserDelete
        $client = static::createClient();
        $currentUser = $this->getUserByEmail('user2@user.com');
        $client->loginUser($currentUser);
        $userToDelete = $this->getUserByEmail('user1@user.com');
        $client->request(
            'DELETE',
            '/api/users/'.$userToDelete->getId().'/delete/',
            [],
            [],
            [],
            '{ "checkPassword" : "user", "_ne_rien_ajouter_" : "" }'
        );
        $this->assertResponseStatusCodeSame(401, 'Mauvais code erreur.');
    }

    /**
     * Supprimer un utilisateur: currentUser(admin) !== userToDelete
     *
     * @return void
     */
    public function testFailedAdminUserDelete() : void
    {
        //php bin/phpunit --filter testFailedAdminUserDelete
        $client = static::createClient();
        $currentUser = $this->getUserByEmail('admin@admin.com');
        $client->loginUser($currentUser);
        $userToDelete = $this->getUserByEmail('user1@user.com');
        $client->request(
            'DELETE',
            '/api/users/'.$userToDelete->getId().'/delete/',
            [],
            [],
            [],
            '{ "checkPassword" : "admin", "_ne_rien_ajouter_" : "" }'
        );
        $this->assertResponseStatusCodeSame(401, 'Mauvais code erreur.');
    }

    /**
     * Supprimer un utilisateur: currentUser === userToDelete
     *
     * @return void
     */
    public function testSuccessfullUserDelete() : void
    {
        //php bin/phpunit --filter testSuccessfullUserDelete
        $client = static::createClient();
        $user = $this->getUserByEmail('user2@user.com');
        $client->loginUser($user);
        $client->request(
            'DELETE',
            '/api/users/'.$user->getId().'/delete/',
            [],
            [],
            [],
            '{ "checkPassword" : "user", "_ne_rien_ajouter_" : "" }'
        );
        $this->assertResponseIsSuccessful();
    }

    /**
     * Supprimer un utilisateur: currentUser(super admin) !== userToDelete
     *
     * @return void
     */
    public function testSuccessfullSuperAdminUserDelete() : void
    {
        //php bin/phpunit --filter testSuccessfullSuperAdminUserDelete
        $client = static::createClient();

        $currentUser = $this->getUserByEmail('super.admin@admin.com');
        $client->loginUser($currentUser);
        $userToDelete = $this->getUserByEmail('user1@user.com');
        $client->request(
            'DELETE',
            '/api/users/'.$userToDelete->getId().'/delete/',
            [],
            [],
            [],
            '{ "checkPassword" : "admin", "_ne_rien_ajouter_" : "" }'
        );
        $this->assertResponseIsSuccessful();
    }

    /**
     * Supprimer un utilisateur: currentUser(super admin) !== userToDelete (super admin)
     *
     * @return void
     */
    public function testSuccessfullDeleteSuperAdmin() : void
    {
        //php bin/phpunit --filter testSuccessfullDeleteSuperAdmin2
        $client = static::createClient();
        $currentUser = $this->getUserByEmail('super.admin@admin.com');
        $client->loginUser($currentUser);
        $userToDelete = $this->getUserByEmail('super.admin2@admin.com');
        $client->request(
            'DELETE',
            '/api/users/'.$userToDelete->getId().'/delete/',
            [],
            [],
            [],
            '{ "checkPassword" : "admin", "_ne_rien_ajouter_" : "" }'
        );
        $this->assertResponseIsSuccessful();
    }
}