# Changelog

All notable changes to this project will be documented in this file.

## [0.1.1] - 2026-04-19

### Fixed
- Added support for DoctrineBundle 3.x in Composer constraints

## [0.1.0] - 2026-04-19

### Added
- Initial Symfony bundle structure
- Timestampable support with `createdAt` and `updatedAt`
- Blameable support with `createdByIdentifier` and `updatedByIdentifier`
- Doctrine listeners based on attributes
- UTC-based lifecycle timestamp handling
- PHPUnit tests for Timestampable and Blameable listeners
- PHPStan, PHP-CS-Fixer and CI setup
