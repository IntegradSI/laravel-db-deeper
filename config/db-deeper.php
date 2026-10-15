<?php

// Configuration for Integrad DB Deeper.
return [
    'dashboard' => [
        'enabled' => true,
        'path' => 'db-deeper',
        'middleware' => ['web', 'auth', 'can:viewDbDeeper'],
    ],

    'backup' => [
        'disk' => 's3',
        'path' => 'database-backups',
        'connection' => null,
    ],

    'monitoring' => [
        'slow_query_threshold_ms' => 500,
        'slow_query_limit' => 10,
    ],
];
