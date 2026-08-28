<?php

return [

    'register_dashboard' => true,

    'navigation' => [
        'group' => 'Security',
        'sort' => 91,
        'icon' => 'heroicon-o-shield-exclamation',
        'label' => 'Antivirus',
    ],

    'permission' => null,

    /*
    |--------------------------------------------------------------------------
    | Scanner driver: clamav | null
    | null = passthrough (dev), clamav = ClamAV daemon or clamscan CLI
    |--------------------------------------------------------------------------
    */
    'driver' => env('ANTIVIRUS_DRIVER', 'clamav'),

    'clamav' => [
        'binary' => env('CLAMAV_BINARY', 'clamscan'),
        'clamdscan_binary' => env('CLAMAV_CLAMDSCAN_BINARY', 'clamdscan'),
        'prefer_clamdscan' => (bool) env('ANTIVIRUS_PREFER_CLAMDSCAN', true),
        'socket' => env('CLAMAV_SOCKET', '/var/run/clamav/clamd.ctl'),
        'timeout' => (int) env('ANTIVIRUS_SCAN_TIMEOUT', 120),
    ],

    /*
    |--------------------------------------------------------------------------
    | Quarantine infected files (move out of original path)
    |--------------------------------------------------------------------------
    */
    'quarantine' => [
        'enabled' => (bool) env('ANTIVIRUS_QUARANTINE', true),
        'disk' => env('ANTIVIRUS_QUARANTINE_DISK', 'local'),
        'directory' => env('ANTIVIRUS_QUARANTINE_DIR', 'quarantine'),
        'retention_days' => (int) env('ANTIVIRUS_QUARANTINE_RETENTION', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default paths for scheduled / manual bulk scans
    |--------------------------------------------------------------------------
    */
    'scan_paths' => [
        storage_path('app/public'),
        storage_path('app/private'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload policy (pre-scan hardening)
    |--------------------------------------------------------------------------
    */
    'blocked_extensions' => [
        'exe', 'bat', 'cmd', 'com', 'msi', 'scr', 'pif', 'vbs', 'js', 'jar',
        'php', 'php3', 'php4', 'php5', 'phtml', 'sh', 'bash', 'ps1',
    ],

    'max_upload_bytes' => (int) env('ANTIVIRUS_MAX_UPLOAD_BYTES', 50 * 1024 * 1024),

];
