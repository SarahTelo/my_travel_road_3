<?php

namespace App\DataFixtures;

use App\Entity\Image;
use App\Entity\Step;
use App\Repository\StepRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ImageFixtures extends Fixture
{
    /**
     * Création de données automatiques en base de données
     * php bin/console doctrine:fixtures:load --append --group=ImageFixtures
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        /** @var StepRepository $repository */
        $steps = $manager->getRepository(Step::class)->findAll();

        $faker = Factory::create('fr_FR');
        for ($i = 1; $i < 10; $i++) {
            $image = new Image();
            $image
                ->setName($faker->sentence())
                ->setPath('image'.$i.'.png')
                ->setDescription($faker->paragraph())
                ->setTakenAt(DateTime::createFromFormat('Y-m-d', $faker->date()))
                ->setStep($steps[array_rand($steps)])
                ->setCreatedAt(DateTimeImmutable::createFromFormat('Y-m-d', $faker->date()))
                ->setUpdatedAt(DateTime::createFromFormat('Y-m-d', $faker->date()))
            ;
            $manager->persist($image);

            //création d'une image physique et upload
            $imageFile = imagecreatetruecolor(100, 100);
            imagetruecolortopalette($imageFile, false, 255);
            imagepng($imageFile, './public/assets/images/image'.$i.'.png');
        }
        $manager->flush();
    }
}
