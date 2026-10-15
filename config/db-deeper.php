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
];
