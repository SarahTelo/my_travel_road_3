<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    /**
     * *Vérifie si le token est valide
     * 
     * @Route("/api/islogged/", name="is_logged", methods={"GET"})
     *
     * @return JSON : données et status code
     */
    public function isLogged(): Response 
    {
        return $this->json( [
            'code' => 200, 
            'message' => 'valid'
        ], 
        Response::HTTP_OK );
    }
}
