<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture
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
        
        $dateFormat = new DateTime('now');
        $today = $dateFormat->getTimestamp() -1;

        //USER

        $user = new User();
        $user->setEmail('admin@admin.com');
        $password = $this->passwordHasher->hashPassword($user, 'test');
        $user->setPassword($password);
        $user->setRoles(["ROLE_ADMIN"]);
        $user->setPseudo($faker->lastName());
        $user->setCreatedAt(new DateTimeImmutable('31-12-2009'));
        $user->setUpdatedAt(null);
        $manager->persist($user);

        $user = new User();
        $user->setEmail('test@test.com');
        $password = $this->passwordHasher->hashPassword($user, 'test');
        $user->setPassword($password);
        $user->setRoles(["ROLE_SUPER_ADMIN"]);
        $user->setPseudo($faker->lastName());
        $user->setCreatedAt(new DateTimeImmutable('31-12-2019'));
        $user->setUpdatedAt(null);
        $manager->persist($user);

        for ($i = 1; $i < 4; $i++) {
            $user = new User();
            $user->setEmail('test'.$i.'@test.com');
            $password = $this->passwordHasher->hashPassword($user, 'test'.$i);
            $user->setPassword($password);
            $user->setPseudo($faker->lastName());
            $user->setRoles(['ROLE_USER']);
            $user->setCreatedAt(DateTimeImmutable::createFromFormat('Y-m-d', $faker->date()));
            $user->setUpdatedAt(DateTimeImmutable::createFromFormat('Y-m-d', $faker->date()));
            $manager->persist($user);
        }

        $manager->flush();
    }
}
