<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Travel;
use App\Repository\TravelRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CategoryFixtures extends Fixture
{
    /**
     * Création de données automatiques en base de données
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        /** @var TravelRepository $repository */
        $travels = $manager->getRepository(Travel::class)->findAll();

        $faker = Factory::create('fr_FR');
        for ($i = 1; $i < 4; $i++) {
            $category = new Category();
            $category->setName($faker->word());
            $category->addTravel($travels[array_rand($travels)]);
            $category->setCreatedAt(DateTimeImmutable::createFromFormat('Y-m-d', $faker->date()));
            $category->setUpdatedAt(DateTime::createFromFormat('Y-m-d', $faker->date()));
            $manager->persist($category);
        }
        $manager->flush();
    }
}
