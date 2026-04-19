# Doctrine Lifecycle Bundle

Reusable Doctrine lifecycle features for Symfony entities.

This bundle is designed to provide a clean and reusable foundation for common entity lifecycle concerns in Symfony applications, such as:

- timestamp management
- actor tracking
- soft-delete metadata
- Doctrine lifecycle integration

## Status

> Work in progress.

The bundle is currently under active development and is being built with real-world usage in mind before wider publication.

## Goals

The main goal of this bundle is to help keep lifecycle-related logic out of application code by offering a dedicated, reusable and maintainable solution for:

- `createdAt` / `updatedAt`
- `createdBy` / `updatedBy`
- `softDeletedAt` / `softDeletedBy`
- Doctrine subscribers for automatic updates
- actor resolution through Symfony Security or a custom resolver

## Philosophy

This bundle focuses on **entity lifecycle metadata** only.

It is intentionally kept separate from:

- audit trail logging
- business workflows
- content publication systems
- translation systems
- other cross-cutting concerns

If you need full audit history, consider using a dedicated audit bundle alongside this one.

## Planned Feature Set

### Timestampable support
Automatic handling of:

- `createdAt`
- `updatedAt`

### Blameable support
Automatic handling of:

- `createdBy`
- `updatedBy`

Depending on the final implementation strategy, blameable data may be stored as:
- identifiers
- scalar values
- or application-level relations handled outside the bundle

### Soft delete metadata
Support for:

- `softDeletedAt`
- `softDeletedBy`

This bundle is intended to help manage soft-delete metadata, not necessarily to enforce a full soft-delete strategy by itself.

### Doctrine integration
A Doctrine subscriber will handle lifecycle events such as:

- `prePersist`
- `preUpdate`

Additional hooks may be introduced later if needed.

## Scope

This bundle aims to stay small, focused and reliable.

It is **not** intended to become a generic “everything bundle” for Doctrine entities.

## Installation

Installation instructions will be completed once the first stable public version is ready.

In the meantime, the bundle can be tested locally in a Symfony project using a Composer path repository.

## Development Approach

This bundle is developed with the following priorities:

1. real usage in Symfony applications
2. minimal and clear feature scope
3. reliable behavior before feature expansion
4. maintainable architecture over magic

## Roadmap

### First milestone
- base bundle structure
- timestampable support
- Doctrine subscriber for creation and update dates

### Second milestone
- blameable support
- actor resolver abstraction

### Third milestone
- soft-delete metadata support
- improved configurability
- test coverage hardening
- documentation completion

## Compatibility

The bundle is being developed for modern Symfony applications using Doctrine ORM.

Exact compatibility targets will be documented once the initial implementation is stabilized.

## Testing

Testing documentation will be added as the bundle evolves.

## Contributing

This project is currently in an early phase. Contributions may be opened later once the initial architecture is stabilized.

## License

This bundle is released under the MIT License.
