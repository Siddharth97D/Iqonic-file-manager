# Laravel Advanced File Manager

A production-ready Laravel file management package with role-based access, image/video compression, and storage quotas.

## Features

- 📂 **Admin Dashboard**: Ready-to-use UI for managing files.
- 🔐 **Role & Permission Aware**: Restrict access to dashboard and file operations.
- 🖼️ **Image & Video Processing**: Compression, resizing, and thumbnails.
- 📊 **Storage Quotas**: Track usage per user and disk.
- 🚀 **High-Speed Uploads**: Chunked/resumable uploads.
- 🔌 **API-First**: Full JSON API for headless usage.
- 🗑️ **Trash Management**: Soft deletes and restore functionality.
- 🔗 **Secure Sharing**: Create time-limited, password-protected share links.

## Requirements

- PHP 8.2+
- Laravel 11.0+
- FFmpeg (optional, for video processing)

## Installation

1. Install the package via Composer:

```bash
composer require iqonic/laravel-advanced-file-manager
```

2. Publish the configuration, migrations, and assets:

```bash
php artisan vendor:publish --provider="Iqonic\FileManager\FileManagerServiceProvider"
```

3. Run the migrations:

```bash
php artisan migrate
```

## Configuration

The configuration file is located at `config/file-manager.php`.

### Key Settings

- **route_prefix**: Change the URL prefix for the dashboard and API (default: `file-manager`).
- **middleware**: Middleware to apply to routes (default: `['web', 'auth']`).
- **enabled_disks**: List of filesystem disks to allow (e.g., `public`, `s3`).
- **quotas**: Set storage limits per user or role.
- **compression**: Define profiles for image and video processing.

## Usage

### Dashboard

Visit `/file-manager` in your browser to access the dashboard. Ensure you are logged in and have the necessary permissions (if configured).

### API

The package provides a full REST API under `/file-manager/api`.

- `GET /files`: List files
- `POST /files/upload`: Upload a file
- `DELETE /files/{id}`: Delete a file
- `GET /stats/usage`: Get storage usage

### Facade

You can use the `FileManager` facade in your code:

```php
use Iqonic\FileManager\Facades\FileManager;

// Upload a file
$file = FileManager::upload($request->file('document'));

// Delete a file
FileManager::delete($file);
```

## Permissions

To restrict access to the dashboard, you can define a gate or permission. By default, it checks for `filemanager.access`.

In your `AppServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('filemanager.access', function ($user) {
    return $user->isAdmin();
});
```

## License

MIT
