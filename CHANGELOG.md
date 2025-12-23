# Changelog

All notable changes to this project will be documented in this file.

## [1.1.0] - 2025-12-23

### Added
- **AWS S3 Primary Storage**: Complete transition to cloud-native storage. Local files are purged immediately after S3 synchronization.
- **S3 Presigned URLs**: Secure, on-the-fly URL generation for all file previews and downloads with 1-hour expiration.
- **Recursive S3 Actions**: Full support for moving and renaming folders directly on S3.
- **Thumbnail S3 Sync**: Synchronization of image and video thumbnails to S3, with direct cloud serving.
- **Shared Link Redirection**: Refactored public shares to serve assets directly from S3.
- **S3 Retention Policy**: Files are preserved in S3 during soft deletion (Trash) and only permanently removed when the trash is emptied.
- **Bulk S3 Sync**: Capability to manually sync existing local data to S3 from the settings page.

### Changed
- Standardized all media assets to use `preview_url` and `thumbnail_url` attributes for unified S3/local serving.
- Updated `FileManagerService` to prioritize S3 as the source of truth for downloads and zipping.
- Improved Alpine.js integration in Trash view to handle large data payloads safely via external script components.

### Fixed
- UI code leak in Trash page caused by nested JSON payload.
- Broken media previews in Trash and Search results.
- Temporary ZIP files cleanup after bulk downloads.
- Reliable dispatching of S3 sync jobs during file upload and processing.

