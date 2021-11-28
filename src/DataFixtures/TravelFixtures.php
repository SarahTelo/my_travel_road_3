<?php

namespace App\DataFixtures;

use App\Entity\Travel;
use App\Entity\User;
use App\Entity\Step;
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
        for ($i = 1; $i < 7; $i++) {
            $travel = new Travel();
            $travel
                ->setTitle($faker->sentence())
                ->setStatus($faker->numberBetween(0,2))
                ->setVisibility($faker->numberBetween(0,1))
                ->setDescription($faker->paragraph())
                ->setUser($users[array_rand($users)])
                ->setCreatedAt(DateTimeImmutable::createFromFormat('Y-m-d', $faker->date()))
                ->setUpdatedAt(DateTime::createFromFormat('Y-m-d', $faker->date()))
            ;
            $manager->persist($travel);

            $step = (new Step())
                ->setTitle('Départ')
                ->setTravel($travel)
                ->setSequence(1)
            ;
            $manager->persist($step);
        }
        $manager->flush();
    }
}
