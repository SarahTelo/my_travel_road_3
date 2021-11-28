<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class UserFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Création de données automatiques en base de données
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        $user = new User();
        $user->setEmail('test@test.com');
        $password = $this->passwordHasher->hashPassword($user, 'test');
        $user
            ->setPassword($password)
            ->setRoles(["ROLE_SUPER_ADMIN"])
            ->setPseudo($faker->lastName())
            ->setCreatedAt(new DateTimeImmutable('31-12-2019'))
            ->setUpdatedAt(null)
        ;
        $manager->persist($user);

        $user = new User();
        $user->setEmail('admin@admin.com');
        $password = $this->passwordHasher->hashPassword($user, 'admin');
        $user
            ->setPassword($password)
            ->setRoles(["ROLE_ADMIN"])
            ->setPseudo($faker->lastName())
            ->setCreatedAt(new DateTimeImmutable('31-12-2009'))
            ->setUpdatedAt(null)
        ;
        $manager->persist($user);

        for ($i = 1; $i < 4; $i++) {
            $user = new User();
            $user->setEmail('test'.$i.'@test.com');
            $password = $this->passwordHasher->hashPassword($user, 'test');
            $user
                ->setPassword($password)
                ->setPseudo($faker->lastName())
                ->setRoles(['ROLE_USER'])
                ->setCreatedAt(DateTimeImmutable::createFromFormat('Y-m-d', $faker->date()))
                ->setUpdatedAt(DateTime::createFromFormat('Y-m-d', $faker->date()))
            ;
            $manager->persist($user);
        }

        //pour les tests User
        $user = new User();
        $user->setEmail('super.admin@admin.com');
        $password = $this->passwordHasher->hashPassword($user, 'admin');
        $user
            ->setPassword($password)
            ->setRoles(["ROLE_SUPER_ADMIN"])
            ->setPseudo($faker->lastName())
            ->setCreatedAt(new DateTimeImmutable('31-12-2019'))
            ->setUpdatedAt(null)
        ;
        $manager->persist($user);

        //pour les tests Travels
        $number = 3;
        for ($i = 0; $i < 2; $i++) {
            $user = new User();
            $user->setEmail('user'.$number.'@user.com');
            $password = $this->passwordHasher->hashPassword($user, 'user');
            $user
                ->setPassword($password)
                ->setPseudo($faker->lastName())
                ->setRoles(['ROLE_USER'])
                ->setCreatedAt(DateTimeImmutable::createFromFormat('Y-m-d', $faker->date()))
                ->setUpdatedAt(DateTime::createFromFormat('Y-m-d', $faker->date()))
            ;
            $manager->persist($user);
            $number++;
        }

        $manager->flush();
    }
}
