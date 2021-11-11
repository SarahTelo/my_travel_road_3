<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RequestListener
{
    /**
     * *Anti spam
     *
     * @param RequestEvent $event
     * @return void
     */
    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if($event->getRequest()->getMethod() == "POST") {
            if($event->getRequest()->request->All()['_ne_rien_ajouter_'] != null) {
                //todo : trouver une solution pour renvoyer un réponse json sans continuer vers le controller associé
                throw new BadRequestHttpException('Qui êtes-vous?');
            }
            
            $contentType = $event->getRequest()->headers->get('Content-Type');
            if(!str_contains($contentType, 'multipart/form-data')) {
                //todo : trouver une solution pour renvoyer un réponse json sans continuer vers le controller associé
                throw new BadRequestHttpException('Nécessite \'multipart/form-data\' dans le header');
            }
        }
        
    }
}