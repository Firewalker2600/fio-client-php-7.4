# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog, and this project follows Semantic Versioning.

---

## [1.0.1] - 2026-04-28

### Changed

- Introduced dependency injection in `FioClient` and `FioSyncManager`
- Refactored `FioSyncManager` to use constructor injection instead of manual instantiation
- Replaced hydrator-based DTO creation with constructor-based immutable DTOs
- Updated all DTOs (`AccountInfoDto`, `AccountStatementDto`, `TransactionDto`) to use readonly constructor pattern (PHP 8.2 style version kept separately)
- Improved internal architecture for better testability and framework compatibility (Symfony / Laravel / Shopware)

### Added

- `FioSyncManagerFactory` for simplified bootstrap without framework dependency
- `FioClientFactory` improvements for easier standalone usage with default PSR implementations
- `TransactionIdExtractorInterface` support for pluggable transaction ID resolution

### Improved

- Better separation between core client logic and framework integration layer
- Cleaner PSR-18 / PSR-7 compliance boundary
- Improved support for Symfony/Laravel autowiring scenarios
- More explicit type safety in DTO mapping layer

### Notes

- This release is **backward-compatible** (no breaking changes in public API)
- Internal architecture was significantly improved, but external usage remains unchanged
- Recommended upgrade for all users of v1.0.0

---

## [1.0.0] - Initial release

### Added

- Fio Bank API PHP client
- Support for:
    - Transaction retrieval by date range
    - Last transaction retrieval
    - Incremental sync via last transaction ID
- DTO-based response mapping
- Basic factory for client initialization
- PSR-18 compatible HTTP client abstraction