<?php

namespace App\DataFixtures;

use App\Entity\Travel;
use App\Entity\User;
use App\Repository\UserRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TravelFixtures extends Fixture
{
    /**
     * Création de données automatiques en base de données
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        /** @var UserRepository $repository */
        $users = $manager->getRepository(User::class)->findAll();

        $faker = Factory::create('fr_FR');
        for ($i = 1; $i < 4; $i++) {
            $travel = new Travel();
            $travel->setTitle($faker->sentence());
            $travel->setStatus($faker->numberBetween(0,2));
            $travel->setVisibility($faker->numberBetween(0,1));
            $travel->setDescription($faker->paragraph());
            $travel->setUser($users[array_rand($users)]);
            $travel->setCreatedAt(DateTimeImmutable::createFromFormat('Y-m-d', $faker->date()));
            $travel->setUpdatedAt(DateTime::createFromFormat('Y-m-d', $faker->date()));
            $manager->persist($travel);
        }
        $manager->flush();
    }
}
