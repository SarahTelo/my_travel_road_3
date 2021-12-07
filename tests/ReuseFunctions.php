<?php

namespace App\Tests;

use App\Repository\UserRepository;
use App\Repository\TravelRepository;

trait ReuseFunctions
{
    private function getUserByEmail(string $email)
    {
        $user = static::getContainer()->get(UserRepository::class)->findOneByEmail($email);
        return $user;
    }

    public function getTravelByVisibilityFalse()
    {
        $travel = static::getContainer()->get(TravelRepository::class)->findOneBy(['visibility' => false]);
        return $travel;
    }

    public function getTravelByUser(int $userId)
    {
        $travel = static::getContainer()->get(TravelRepository::class)->findOneBy(['user' => $userId]);
        return $travel;
    }
}