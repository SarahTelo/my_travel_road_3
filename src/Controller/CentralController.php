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

//todo: au lieu de mettre les 2 derniers voyages et inscrits, mettre (en html) un voyage et un utilisateur préfabriqué pour donner un exemple

class CentralController extends AbstractController
{
    /**
     * @Route("/", name="welcome")
     */
    public function welcome(Request $request): Response
    {
        return $this->json('Page de test, elle n\'est pas nécessaire pour une API. La home est faite en front.');
    }

    /**
     * *Page d'accueil avec la liste des 2 derniers voyages ajoutés et les 2 derniers utilisateurs inscrits
     * 
     * @Route("/api/", name="home")
     */
    public function home()
    {
        /** @var UserRepository */
        $users = $this->getDoctrine()->getRepository(User::class)->findBy([], ['id' => 'DESC'], 2, null);
        /** @var TravelRepository */
        $travels = $this->getDoctrine()->getRepository(Travel::class)->findBy(['visibility' => 1], ['id' => 'DESC'], 2, null);
        return $this->json([
            'currentUserHomeDetail' => $this->getUser(),
            'usersHomeList' => $users,
            'travelsHomeList' => $travels,
        ], Response::HTTP_OK, [], ['groups' => 'home_detail']);
    }
}
