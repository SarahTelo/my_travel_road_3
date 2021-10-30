<?php

namespace App\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use DateTime;

class AutoSetListener 
{
    /**
     * *Auto mise à jour des champs "updated_at" et "name"
     *
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function preUpdate(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();
        $entity->setUpdatedAt(new DateTime('now'));
        //if (!$entity instanceof $entity) { return; }
    }
}