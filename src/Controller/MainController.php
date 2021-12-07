<?php

namespace App\Controller;

use App\Entity\Travel;
use App\Entity\User;
use App\Repository\TravelRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="welcome")
     */
    public function welcome(Request $request): Response
    {
        return $this->json('Page de test, elle n\'est pas nécessaire pour une API. La home est faite en front.');
    }

    /**
     * *Page d'accueil avec un utilisateur et un voyage au hasard
     * 
     * @Route("/api/", name="home")
     */
    public function home(): Response
    {
        /** @var TravelRepository */
        $travelRepository = $this->getDoctrine()->getRepository(Travel::class);
        $travelsId = $travelRepository->findAllTravelsIdByVisibility();
        $numberOfTravel = array_rand($travelsId, 1);
        $travel = $travelRepository->find($travelsId[$numberOfTravel]['id']);

        /** @var UserRepository */
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $usersId = $userRepository->findAllUsersId();
        $numberOfUser = array_rand($usersId, 1);
        $user = $userRepository->find($usersId[$numberOfUser]['id']);

        return $this->json([
            'currentUserHomeDetail' => $this->getUser(),
            'usersHomeList' => $user,
            'travelsHomeList' => $travel,
        ], Response::HTTP_OK, [], ['groups' => 'home_detail']);
    }
}
