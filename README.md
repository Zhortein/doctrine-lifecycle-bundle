# Zhortein Doctrine Lifecycle Bundle

Reusable Doctrine lifecycle features for Symfony entities.

This bundle provides a clean and focused foundation for common entity lifecycle concerns in Symfony applications using Doctrine ORM.

It currently includes:

- **Timestampable** support
- **Blameable** support
- Doctrine lifecycle listeners based on attributes
- UTC-based lifecycle timestamps

The bundle is designed to stay small, explicit and reusable.

---

## Features

### Timestampable
Automatic handling of:

- `createdAt`
- `updatedAt`

### Blameable
Automatic handling of:

- `createdByIdentifier`
- `updatedByIdentifier`

Blameable values are stored as scalar identifiers, which keeps the bundle generic and independent from any application-specific `User` entity.

### Doctrine integration
The bundle uses Doctrine listeners to update lifecycle fields automatically on:

- `prePersist`
- `preUpdate`

### UTC timestamps
Lifecycle dates are generated in **UTC** to provide predictable and consistent storage across applications.

---

## Current Scope

This bundle focuses only on **entity lifecycle metadata**.

It does **not** try to handle:

- audit trail history
- workflow/state machines
- publication systems
- translations
- business-specific ownership models

If you need full audit logging, use a dedicated audit bundle alongside this one.

---

## Requirements

- PHP **8.4+**
- Symfony **7.4** or **8.0**
- Doctrine Bundle
- Doctrine ORM

---

## Installation

### Composer

```bash
composer require zhortein/doctrine-lifecycle-bundle
```

### Bundle registration

If Symfony Flex does not register the bundle automatically, add it manually in `config/bundles.php`:

```php
<?php

return [
    // ...
    Zhortein\DoctrineLifecycleBundle\ZhorteinDoctrineLifecycleBundle::class => ['all' => true],
];
```

---

## Default Behavior

### Timestampable
On `prePersist`:
- `createdAt` is set if it is currently `null`
- `updatedAt` is always set

On `preUpdate`:
- `updatedAt` is updated

### Blameable
On `prePersist`:
- `createdByIdentifier` is set if it is currently `null`
- `updatedByIdentifier` is always set

On `preUpdate`:
- `updatedByIdentifier` is updated

If no actor can be resolved, blameable fields remain unchanged.

---

## Usage

## Timestampable

### 1. Implement the interface and use the trait

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zhortein\DoctrineLifecycleBundle\Contract\TimestampableInterface;
use Zhortein\DoctrineLifecycleBundle\Trait\TimestampableTrait;

#[ORM\Entity]
final class Destination implements TimestampableInterface
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
```

### 2. Result
The bundle will automatically maintain:

- `createdAt`
- `updatedAt`

---

## Blameable

### 1. Implement the interface and use the trait

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zhortein\DoctrineLifecycleBundle\Contract\BlameableInterface;
use Zhortein\DoctrineLifecycleBundle\Trait\BlameableTrait;

#[ORM\Entity]
final class Article implements BlameableInterface
{
    use BlameableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title;

    public function __construct(string $title)
    {
        $this->title = $title;
    }
}
```

### 2. Result
The bundle will automatically maintain:

- `createdByIdentifier`
- `updatedByIdentifier`

These values are provided by an `ActorResolverInterface`.

---

## Timestampable + Blameable together

A single entity can use both features:

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zhortein\DoctrineLifecycleBundle\Contract\BlameableInterface;
use Zhortein\DoctrineLifecycleBundle\Contract\TimestampableInterface;
use Zhortein\DoctrineLifecycleBundle\Trait\BlameableTrait;
use Zhortein\DoctrineLifecycleBundle\Trait\TimestampableTrait;

#[ORM\Entity]
final class ExpertProfile implements TimestampableInterface, BlameableInterface
{
    use TimestampableTrait;
    use BlameableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $displayName;

    public function __construct(string $displayName)
    {
        $this->displayName = $displayName;
    }
}
```

---

## Actor Resolver

The bundle relies on the following contract:

```php
<?php

declare(strict_types=1);

namespace Zhortein\DoctrineLifecycleBundle\Resolver;

interface ActorResolverInterface
{
    public function resolveActorIdentifier(): ?string;
}
```

By default, the bundle provides a **null resolver**, which means blameable fields remain `null` unless you override the service.

---

## Using Symfony Security as actor resolver

In most applications, you will want to resolve the actor identifier from the authenticated user.

Example:

```php
<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Bundle\SecurityBundle\Security;
use Zhortein\DoctrineLifecycleBundle\Resolver\ActorResolverInterface;

final class SecurityActorResolver implements ActorResolverInterface
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function resolveActorIdentifier(): ?string
    {
        $user = $this->security->getUser();

        if (null === $user) {
            return null;
        }

        if (!method_exists($user, 'getUserIdentifier')) {
            return null;
        }

        return $user->getUserIdentifier();
    }
}
```

Then override the resolver binding in your application:

```yaml
services:
    App\Security\SecurityActorResolver: ~

    Zhortein\DoctrineLifecycleBundle\Resolver\ActorResolverInterface:
        alias: App\Security\SecurityActorResolver
```

---

## Notes about timestamps

This bundle stores lifecycle timestamps as:

- `\DateTimeImmutable`
- Doctrine `datetime_immutable`
- generated in **UTC**

This is intentional.

The bundle handles **technical lifecycle timestamps**, not application-level timezone presentation.  
If your application needs to display dates in a specific timezone, convert them at application level.

Example:

```php
$localDate = $entity->getUpdatedAt()?->setTimezone(new \DateTimeZone('Europe/Paris'));
```

---

## Philosophy

This bundle intentionally follows a few rules:

- keep the scope narrow
- avoid application-specific assumptions
- stay independent from the host application's `User` entity
- prefer predictable behavior over excessive magic
- make integration simple in real Symfony projects

---

## Development Status

The bundle is already usable for:

- timestampable metadata
- blameable metadata
- real integration into Symfony applications

Additional lifecycle helpers may come later, but only if they remain aligned with the bundle's narrow scope.

---

## Roadmap

Possible future additions:

- soft delete metadata support
- optional configuration improvements
- additional integration tests
- more documentation and recipes

---

## Quality Assurance

This bundle includes:

- PHPUnit tests
- PHPStan configuration
- PHP-CS-Fixer configuration
- CI workflow

Typical local commands:

```bash
make csfixer
make phpstan
make test
```

---

## Testing locally with a path repository

When developing the bundle alongside a Symfony project, you can use a Composer path repository in the host application:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../doctrine-lifecycle-bundle",
      "options": {
        "symlink": true
      }
    }
  ],
  "require": {
    "zhortein/doctrine-lifecycle-bundle": "*@dev"
  }
}
```

This allows you to test the bundle directly in a real application without publishing it first.

---

## License

This bundle is released under the **MIT License**.
