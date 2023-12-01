<?php

namespace Spyck\VisualizationBundle\Listener;

use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Spyck\VisualizationBundle\Entity\TimestampInterface;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
final class TimestampListener
{
    public function prePersist(PrePersistEventArgs $prePersistEventArgs): void
    {
        $object = $prePersistEventArgs->getObject();

        if ($object instanceof TimestampInterface) {
            $date = new DateTimeImmutable();

            $object->setTimestampCreated($date);
        }
    }

    public function preUpdate(PreUpdateEventArgs $preUpdateEventArgs): void
    {
        $object = $preUpdateEventArgs->getObject();

        if ($object instanceof TimestampInterface) {
            $date = new DateTimeImmutable();

            $object->setTimestampUpdated($date);
        }
    }
}
