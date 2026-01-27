# Changelog

All notable changes to `aware/custom-id` will be documented in this file.

## [Unreleased]

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
