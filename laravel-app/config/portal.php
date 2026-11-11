<?php

$setupKey = env('APP_SETUP_KEY');

return [
    'migration_key_hash' => is_string(env('PORTAL_MIGRATION_KEY')) && preg_match('/\A[a-fA-F0-9]{64}\z/', env('PORTAL_MIGRATION_KEY')) === 1
        ? hash('sha256', env('PORTAL_MIGRATION_KEY'))
        : null,
    'setup_key_hash' => is_string($setupKey) && preg_match('/\A[a-fA-F0-9]{64}\z/', $setupKey) === 1
        ? hash('sha256', $setupKey)
        : null,
    'roles' => [
        'super_admin' => env('PORTAL_SUPER_ADMIN_ROLE', 'Super Admin'),
        'admin' => env('PORTAL_ADMIN_ROLE', 'Admin'),
    ],
    'seed_accounts' => [
        'admin_password' => env('PORTAL_SEED_ADMIN_PASSWORD'),
        'super_admin_password' => env('PORTAL_SEED_SUPER_ADMIN_PASSWORD'),
    ],
    'media' => [
        'disk' => 'local',
        'max_upload_kb' => (int) env('PORTAL_MAX_UPLOAD_KB', 20480),
        'max_video_upload_kb' => (int) env('PORTAL_MAX_VIDEO_UPLOAD_KB', 51200),
        'max_image_dimension' => (int) env('PORTAL_MAX_IMAGE_DIMENSION', 4000),
        'max_optimized_dimension' => (int) env('PORTAL_MAX_OPTIMIZED_IMAGE_DIMENSION', 2400),
        'private_root' => storage_path('app/private'),
    ],
];
