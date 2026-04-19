<?php

declare(strict_types=1);

namespace Zhortein\DoctrineLifecycleBundle\Resolver;

final class NullActorResolver implements ActorResolverInterface
{
    public function resolveActorIdentifier(): ?string
    {
        return null;
    }
}
