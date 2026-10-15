<?php

// Configuration for Integrad Laravel Database Backup.
return [
    'dashboard' => [
        'enabled' => true,
        'path' => 'database-backup',
        'middleware' => ['web', 'auth', 'can:viewDatabaseBackups'],
    ],

    'backup' => [
        'disk' => 's3',
        'path' => 'database-backups',
        'connection' => null,
    ],
];
