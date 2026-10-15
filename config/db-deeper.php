<?php

// Configuration for Integrad DB Deeper.
return [
    'dashboard' => [
        'enabled' => true,
        'path' => 'db-deeper',
        'middleware' => ['web', 'auth', 'can:viewDbDeeper'],
        'refresh_interval_seconds' => 30,
    ],

    'backup' => [
        'scheduler_enabled' => true,
        'disk' => 's3',
        'path' => 'database-backups',
        'connection' => null,
        'encryption_key' => null,
        'chunk_size' => 1048576,
    ],

    'monitoring' => [
        'slow_query_threshold_ms' => 500,
        'slow_query_limit' => 10,
    ],
];
