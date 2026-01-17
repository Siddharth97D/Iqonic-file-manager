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
    | Image Variants
    |--------------------------------------------------------------------------
    |
    | Configure automatic generation of image variants with different sizes.
    | Each preset defines width, height, and fit mode (crop, contain, cover).
    |
    */
    'image_variants' => [
        'enabled' => env('IMAGE_VARIANTS_ENABLED', true),
        'presets' => [
            'thumbnail' => [
                'width' => 150,
                'height' => 150,
                'fit' => 'crop', // crop, contain, cover
                'quality' => 80,
            ],
            'small' => [
                'width' => 400,
                'height' => 400,
                'fit' => 'contain',
                'quality' => 85,
            ],
            'medium' => [
                'width' => 800,
                'height' => 800,
                'fit' => 'contain',
                'quality' => 85,
            ],
            'large' => [
                'width' => 1600,
                'height' => 1600,
                'fit' => 'contain',
                'quality' => 90,
            ],
        ],
        // Generate WebP variants alongside original format
        'generate_webp' => true,
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
        'enable_favorites' => env('ENABLE_FAVORITES', true),
        'enable_dark_mode' => env('ENABLE_DARK_MODE', true),
        'enable_keyboard_shortcuts' => env('ENABLE_KEYBOARD_SHORTCUTS', true),
        'enable_api' => env('ENABLE_API', true),
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
