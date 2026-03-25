<?php

return [
    /*
    |--------------------------------------------------------------------------
    | License Server
    |--------------------------------------------------------------------------
    */
    'license' => [
        'server_url' => env('IMS_LICENSE_SERVER_URL', 'https://license.techmiddle.com'),
        'offline_grace_days' => 7,
        'check_interval_hours' => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup
    |--------------------------------------------------------------------------
    */
    'backup' => [
        'local_path' => env('IMS_BACKUP_PATH', null), // null = %APPDATA%\IMS\backups
        'max_local_backups' => 7,
        'warn_after_hours' => 12,
        's3_bucket' => env('IMS_BACKUP_S3_BUCKET', 'ims-backups'),
        's3_region' => env('IMS_BACKUP_S3_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption
    |--------------------------------------------------------------------------
    | The app secret is used to derive encryption keys for license storage
    | and backup files. In production builds, this is obfuscated.
    */
    'app_secret' => env('IMS_APP_SECRET', 'ims-techmiddle-2026-xK9pL2mN8qR4'),

    /*
    |--------------------------------------------------------------------------
    | Sentry Error Tracking
    |--------------------------------------------------------------------------
    | DSN is set here and injected into build .env. Leave empty to disable.
    | Get your DSN from https://sentry.io
    */
    'sentry_dsn' => env('SENTRY_LARAVEL_DSN', 'https://e343747227bcc22ef8ab6cedc1dbdb4e@o4506540143542272.ingest.us.sentry.io/4511100662317056'),

    /*
    |--------------------------------------------------------------------------
    | Demo Data Seeding
    |--------------------------------------------------------------------------
    | Set IMS_DEMO_SEED=true in .env to seed demo data on first setup.
    | Used instead of APP_DEBUG because NativePHP overrides APP_DEBUG at runtime.
    */
    'demo_seed' => env('IMS_DEMO_SEED', false),
];
