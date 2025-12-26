# Changelog

All notable changes to this project will be documented in this file.

## [1.1.4] - 2025-12-26

### Fixed
- **Critical**: Restored missing variable definitions in `ProcessImageJob` that caused "undefined variable $compressEnabled" error on fresh installations

## [1.1.3] - 2025-12-26

### Added
- **Enhanced PDF Preview**: PDFs now display significantly larger in the preview modal with improved readability
- **PDF Viewer Controls**: Added built-in PDF navigation toolbar, navigation panes, and optimized view parameters
- **Missing Migration**: Added `file_manager_settings` table migration that was previously missing

### Changed
- **PDF Display**: Removed size constraints and set minimum height of 600px for better PDF viewing experience
- **PDF Toolbar**: Added filename and document type display in preview modal

## [1.1.2] - 2025-12-23

### Added
- **Configurable Video Processing**: Added settings to enable/disable video thumbnails.
- **FFmpeg Customization**: Users can now specify custom paths for `ffmpeg` and `ffprobe` binaries in settings.
- **Diagnostic Tools**: Added a "Test FFmpeg" button in settings to verify binary connectivity and version info.
- **Setup Guidance**: Added direct links to FFmpeg documentation and setup help within the UI.

### Changed
- **Sequential Media Core**: Refactored `FileManagerService` to use Laravel Job Chaining. This ensures media processing (Compression, WebP, Thumbnails) completes *before* S3 synchronization, preventing data loss from premature local file deletion.
- **Job Reliability**: Processing jobs now gracefully handle missing disk files and binary execution errors.

## [1.1.1] - 2025-12-23

### Added
- **Hybrid S3 Serving**: Synced assets now continue to load from S3 even when synchronization is disabled in settings.

### Fixed
- "Call to a member function getCommand() on null" error in `S3SyncService` when S3 is disabled.
- Missing `bulkDownload` method and duplicate `dispatchS3Sync` declaration in `FileManagerService`.
- Strict `s3_enabled` guards for all new sync actions and bulk sync requests.

## [1.1.0] - 2025-12-23


