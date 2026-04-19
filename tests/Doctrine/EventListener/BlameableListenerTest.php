<?php

declare(strict_types=1);

namespace Zhortein\DoctrineLifecycleBundle\Tests\Doctrine\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Zhortein\DoctrineLifecycleBundle\Contract\BlameableInterface;
use Zhortein\DoctrineLifecycleBundle\Doctrine\EventListener\BlameableListener;
use Zhortein\DoctrineLifecycleBundle\Resolver\ActorResolverInterface;
use Zhortein\DoctrineLifecycleBundle\Trait\BlameableTrait;

final class BlameableListenerTest extends TestCase
{
    public function testPrePersistSetsCreatedByAndUpdatedBy(): void
    {
        $resolver = new TestActorResolver('david@example.com');
        $listener = new BlameableListener($resolver);
        $entity = new BlameableTestEntity();

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $args = new PrePersistEventArgs($entity, $entityManager);

        $listener->prePersist($args);

        self::assertSame('david@example.com', $entity->getCreatedByIdentifier());
        self::assertSame('david@example.com', $entity->getUpdatedByIdentifier());
    }

    public function testPrePersistDoesNotOverrideCreatedByIfAlreadySet(): void
    {
        $resolver = new TestActorResolver('david@example.com');
        $listener = new BlameableListener($resolver);
        $entity = new BlameableTestEntity();

        $entity->setCreatedByIdentifier('existing@example.com');

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $args = new PrePersistEventArgs($entity, $entityManager);

        $listener->prePersist($args);

        self::assertSame('existing@example.com', $entity->getCreatedByIdentifier());
        self::assertSame('david@example.com', $entity->getUpdatedByIdentifier());
    }

    public function testPreUpdateSetsUpdatedByAndRecomputesChangeset(): void
    {
        $resolver = new TestActorResolver('david@example.com');
        $listener = new BlameableListener($resolver);
        $entity = new BlameableTestEntity();

        $unitOfWork = $this->createMock(UnitOfWork::class);
        $metadata = $this->createStub(ClassMetadata::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $entityManager
            ->expects(self::once())
            ->method('getClassMetadata')
            ->with($entity::class)
            ->willReturn($metadata);

        $entityManager
            ->expects(self::once())
            ->method('getUnitOfWork')
            ->willReturn($unitOfWork);

        $unitOfWork
            ->expects(self::once())
            ->method('recomputeSingleEntityChangeSet')
            ->with($metadata, $entity);

        $changeSet = [];
        $args = new PreUpdateEventArgs($entity, $entityManager, $changeSet);

        $listener->preUpdate($args);

        self::assertSame('david@example.com', $entity->getUpdatedByIdentifier());
    }

    public function testDoesNothingWhenNoActorIsResolved(): void
    {
        $resolver = new TestActorResolver(null);
        $listener = new BlameableListener($resolver);
        $entity = new BlameableTestEntity();

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $args = new PrePersistEventArgs($entity, $entityManager);

        $listener->prePersist($args);

        self::assertNull($entity->getCreatedByIdentifier());
        self::assertNull($entity->getUpdatedByIdentifier());
    }
}

final class BlameableTestEntity implements BlameableInterface
{
    use BlameableTrait;
}

final class TestActorResolver implements ActorResolverInterface
{
    public function __construct(
        private readonly ?string $actorIdentifier,
    ) {
    }

    public function resolveActorIdentifier(): ?string
    {
        return $this->actorIdentifier;
    }
}
