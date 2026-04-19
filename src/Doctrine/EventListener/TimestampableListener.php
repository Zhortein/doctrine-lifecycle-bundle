<?php

declare(strict_types=1);

namespace Zhortein\DoctrineLifecycleBundle\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Zhortein\DoctrineLifecycleBundle\Contract\TimestampableInterface;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
final class TimestampableListener
{
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof TimestampableInterface) {
            return;
        }

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        if (null === $entity->getCreatedAt()) {
            $entity->setCreatedAt($now);
        }

        $entity->setUpdatedAt($now);
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof TimestampableInterface) {
            return;
        }

        $entity->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));

        $em = $args->getObjectManager();
        $metadata = $em->getClassMetadata($entity::class);
        $em->getUnitOfWork()->recomputeSingleEntityChangeSet($metadata, $entity);
    }
}
