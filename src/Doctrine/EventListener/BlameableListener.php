<?php

declare(strict_types=1);

namespace Zhortein\DoctrineLifecycleBundle\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Zhortein\DoctrineLifecycleBundle\Contract\BlameableInterface;
use Zhortein\DoctrineLifecycleBundle\Resolver\ActorResolverInterface;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
final class BlameableListener
{
    public function __construct(
        private readonly ActorResolverInterface $actorResolver,
    ) {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof BlameableInterface) {
            return;
        }

        $actorIdentifier = $this->actorResolver->resolveActorIdentifier();

        if (null === $actorIdentifier) {
            return;
        }

        if (null === $entity->getCreatedByIdentifier()) {
            $entity->setCreatedByIdentifier($actorIdentifier);
        }

        $entity->setUpdatedByIdentifier($actorIdentifier);
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof BlameableInterface) {
            return;
        }

        $actorIdentifier = $this->actorResolver->resolveActorIdentifier();

        if (null === $actorIdentifier) {
            return;
        }

        $entity->setUpdatedByIdentifier($actorIdentifier);

        $entityManager = $args->getObjectManager();
        $metadata = $entityManager->getClassMetadata($entity::class);
        $entityManager->getUnitOfWork()->recomputeSingleEntityChangeSet($metadata, $entity);
    }
}
