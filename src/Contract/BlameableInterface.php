<?php

declare(strict_types=1);

namespace Zhortein\DoctrineLifecycleBundle\Contract;

interface BlameableInterface
{
    public function getCreatedByIdentifier(): ?string;

    public function setCreatedByIdentifier(?string $createdByIdentifier): static;

    public function getUpdatedByIdentifier(): ?string;

    public function setUpdatedByIdentifier(?string $updatedByIdentifier): static;
}
