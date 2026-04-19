<?php

declare(strict_types=1);

namespace Zhortein\DoctrineLifecycleBundle\Tests\Doctrine\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Zhortein\DoctrineLifecycleBundle\Contract\TimestampableInterface;
use Zhortein\DoctrineLifecycleBundle\Doctrine\EventListener\TimestampableListener;
use Zhortein\DoctrineLifecycleBundle\Trait\TimestampableTrait;

final class TimestampableListenerTest extends TestCase
{
    public function testPrePersistSetsCreatedAtAndUpdatedAt(): void
    {
        $listener = new TimestampableListener();
        $entity = new TimestampableTestEntity();

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $args = new PrePersistEventArgs($entity, $entityManager);

        $listener->prePersist($args);

        self::assertInstanceOf(\DateTimeImmutable::class, $entity->getCreatedAt());
        self::assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());
        self::assertSame('UTC', $entity->getCreatedAt()->getTimezone()->getName());
        self::assertSame('UTC', $entity->getUpdatedAt()->getTimezone()->getName());
    }

    public function testPrePersistDoesNotOverrideCreatedAtIfAlreadySet(): void
    {
        $listener = new TimestampableListener();
        $entity = new TimestampableTestEntity();

        $existingCreatedAt = new \DateTimeImmutable('2026-01-01 10:00:00', new \DateTimeZone('UTC'));
        $entity->setCreatedAt($existingCreatedAt);

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $args = new PrePersistEventArgs($entity, $entityManager);

        $listener->prePersist($args);

        self::assertSame($existingCreatedAt, $entity->getCreatedAt());
        self::assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());
    }

    public function testPreUpdateSetsUpdatedAtAndRecomputesChangeset(): void
    {
        $listener = new TimestampableListener();
        $entity = new TimestampableTestEntity();

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

        self::assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());
        self::assertSame('UTC', $entity->getUpdatedAt()->getTimezone()->getName());
    }
}

final class TimestampableTestEntity implements TimestampableInterface
{
    use TimestampableTrait;
}