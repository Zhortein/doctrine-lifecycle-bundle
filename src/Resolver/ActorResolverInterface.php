<?php

declare(strict_types=1);

namespace Zhortein\DoctrineLifecycleBundle\Resolver;

interface ActorResolverInterface
{
    public function resolveActorIdentifier(): ?string;
}
