<?php

namespace App\DataFixtures;

use App\Entity\Country;
use App\Entity\User;
use App\Repository\UserRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CountryFixtures extends Fixture
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
            $country = new Country();
            $country
                ->setName($faker->country())
                ->setCoordinate($faker->latitude().'/'.$faker->longitude())
                ->addUser($users[array_rand($users)])
                ->setCreatedAt(DateTimeImmutable::createFromFormat('Y-m-d', $faker->date()))
                ->setUpdatedAt(DateTime::createFromFormat('Y-m-d', $faker->date()))
            ;
            $manager->persist($country);
        }
        $manager->flush();
    }
}
