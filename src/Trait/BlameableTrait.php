<?php

declare(strict_types=1);

namespace Zhortein\DoctrineLifecycleBundle\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait BlameableTrait
{
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $createdByIdentifier = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $updatedByIdentifier = null;

    public function getCreatedByIdentifier(): ?string
    {
        return $this->createdByIdentifier;
    }

    public function setCreatedByIdentifier(?string $createdByIdentifier): static
    {
        $this->createdByIdentifier = $createdByIdentifier;

        return $this;
    }

    public function getUpdatedByIdentifier(): ?string
    {
        return $this->updatedByIdentifier;
    }

    public function setUpdatedByIdentifier(?string $updatedByIdentifier): static
    {
        $this->updatedByIdentifier = $updatedByIdentifier;

        return $this;
    }
}
