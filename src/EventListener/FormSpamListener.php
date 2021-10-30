<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Exception;

class FormSpamListener implements EventSubscriberInterface
{
    /**
     * Initialisation
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SUBMIT => 'onPostSubmit',
        ];
    }

    /**
     * *Vérification du champ anti spam juste après la soumission du formulaire
     *
     * @param FormEvent $event
     * @return void
     */
    public function onPostSubmit(FormEvent $event) : void
    {
        $form = $event->getForm();
        if ($form->get('_ne_rien_ajouter_')->getViewData() != null) {
            //todo : trouver un moyen de faire une redirection
            //$this->addFlash('dark', 'Qui êtes-vous?');
            //return $this->redirectToRoute('home');
            //*prod
            throw new Exception('voir FormSpamListener!', 405);
        }
    }
}