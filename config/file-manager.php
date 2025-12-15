<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix for the file manager dashboard and API routes.
    |
    */
    'route_prefix' => 'file-manager',

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | The middleware to apply to the file manager routes.
    |
    */
    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Permission
    |--------------------------------------------------------------------------
    |
    | The permission required to access the dashboard.
    | If using spatie/laravel-permission, this permission will be checked.
    |
    */
    'dashboard_permission' => 'filemanager.access',

    /*
    |--------------------------------------------------------------------------
    | Disk Configuration
    |--------------------------------------------------------------------------
    |
    | The list of disks that are available for file storage.
    |
    */
    'enabled_disks' => ['public', 's3'],

    'default_disk' => 'public',

    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for file uploads.
    |
    */
    'upload' => [
        'max_size_mb' => 100,
        'allowed_mimes' => [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf',
            'text/plain',
            'video/mp4', 'video/quicktime'
        ],
        'chunk_size_mb' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Quotas
    |--------------------------------------------------------------------------
    |
    | Storage quotas for users.
    |
    */
    'quotas' => [
        'default_user_quota_mb' => 1000,
        'by_role' => [
            'admin' => 10000,
            'premium' => 5000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Compression Profiles
    |--------------------------------------------------------------------------
    |
    | Profiles for image and video compression.
    |
    */
    'compression' => [
        'image_profiles' => [
            'thumb' => ['quality' => 80, 'max_width' => 150, 'max_height' => 150, 'format' => 'webp'],
            'medium' => ['quality' => 85, 'max_width' => 800, 'max_height' => 800, 'format' => 'webp'],
            'large' => ['quality' => 90, 'max_width' => 1920, 'max_height' => 1080, 'format' => 'webp'],
        ],
        'video_profiles' => [
            '720p' => ['resolution' => '1280x720', 'bitrate' => '1500k', 'codec' => 'h264'],
            '1080p' => ['resolution' => '1920x1080', 'bitrate' => '3000k', 'codec' => 'h264'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Trash Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the recycle bin.
    |
    */
    'trash' => [
        'enabled' => true,
        'days_to_keep' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific features.
    |
    */
    'features' => [
        'enable_video_processing' => true,
        'enable_direct_s3_upload' => false,
        'enable_hls' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy
    |--------------------------------------------------------------------------
    |
    | Configuration for multi-tenancy support.
    |
    */
    'multi_tenancy' => [
        'enabled' => false,
        'tenant_column' => 'tenant_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | FFmpeg Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for FFmpeg and FFProbe binaries.
    |
    */
    'ffmpeg' => [
        'ffmpeg_path' => env('FFMPEG_PATH', '/usr/bin/ffmpeg'),
        'ffprobe_path' => env('FFPROBE_PATH', '/usr/bin/ffprobe'),
    ],
];
