<?php

return [
    'disk' => env('MEDIA_DISK', 'public'),
    'path' => env('MEDIA_PATH', 'media'),
    'max_file_size' => env('MEDIA_MAX_FILE_SIZE', 10 * 1024 * 1024),

    'allowed_types' => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        'video/mp4',
        'video/webm',
        'audio/mpeg',
        'audio/wav',
        'application/pdf',
    ],

    'thumbnails' => [
        'enabled' => true,
        'width' => 300,
        'height' => 300,
        'quality' => 80,
    ],

    'optimization' => [
        'enabled' => true,
        'max_width' => 2000,
        'max_height' => 2000,
        'quality' => 85,
    ],

    'storage_limits' => [
        'free' => 100 * 1024 * 1024,
        'pro' => 5 * 1024 * 1024 * 1024,
        'business' => 50 * 1024 * 1024 * 1024,
    ],
];
