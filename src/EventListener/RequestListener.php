<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RequestListener
{
    /**
     * *Vérifications avant soumission d'un formulaire
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
            //anti-spam
            if($event->getRequest()->request->All()['_ne_rien_ajouter_'] != null) {
                //todo
                throw new BadRequestHttpException('Qui êtes-vous?');
            }
            //content type
            $contentType = $event->getRequest()->headers->get('Content-Type');
            if(!str_contains($contentType, 'multipart/form-data')) {
                //todo
                throw new BadRequestHttpException('Nécessite \'multipart/form-data\' dans le header');
            }
        }
        
    }
}