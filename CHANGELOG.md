# Changelog

All notable changes to this project will be documented in this file.

## [1.1.1] - 2025-12-23

### Added
- **Hybrid S3 Serving**: Synced assets now continue to load from S3 even when synchronization is disabled in settings.

### Fixed
- "Call to a member function getCommand() on null" error in `S3SyncService` when S3 is disabled.
- Missing `bulkDownload` method and duplicate `dispatchS3Sync` declaration in `FileManagerService`.
- Strict `s3_enabled` guards for all new sync actions and bulk sync requests.

## [1.1.0] - 2025-12-23


