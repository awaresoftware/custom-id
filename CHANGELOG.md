# Changelog

All notable changes to `aware/custom-id` will be documented in this file.

## [Unreleased]

## [1.1.0] - 2026-02-05

### Added
- **Optional users table migration** - Convert existing `users` table from integer auto-increment to custom string IDs
- Publishable migration via `php artisan vendor:publish --tag=custom-id-users-migration`
- Auto-detection of common Laravel tables (sessions, personal_access_tokens, notifications, oauth_*)
- Configurable related tables for custom foreign key handling
- Support for polymorphic relationships in migration
- Database-agnostic migration (MySQL, PostgreSQL, SQLite)
- User-specific configuration in `config/custom-id.php` (`users` and `users_migration` sections)

### Changed
- Updated config file with `users` and `users_migration` configuration sections
- Updated README with comprehensive users migration documentation

## [1.0.0] - 2026-02-01

### Added
- Initial release
- Custom ID generation with configurable character sets, lengths, and prefixes
- `HasCustomId` trait for Eloquent models
- Soft-delete awareness to prevent ID reuse
- Race condition handling with retry mechanism
- Custom `CustomIdGenerationException` for detailed error information
- Automatic default `getCustomIdType()` based on class name
- Support for per-model configuration via `getCustomIdConfig()`
- Facade support via `CustomId` facade
- Comprehensive documentation and examples
