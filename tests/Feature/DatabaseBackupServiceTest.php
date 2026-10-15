<?php

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Integrad\DbDeeper\Services\BackupEncryptionKeyService;
use Integrad\DbDeeper\Services\BackupEncryptionService;
use Integrad\DbDeeper\Services\DatabaseBackupService;

it('isolates mysql dumps from global client database options', function () {
    $service = new DatabaseBackupService(
        Mockery::mock(FilesystemFactory::class),
        Mockery::mock(BackupEncryptionService::class),
        Mockery::mock(BackupEncryptionKeyService::class),
    );
    $credentialPath = null;

    try {
        $command = (new ReflectionMethod(DatabaseBackupService::class, 'mysqlCommand'))
            ->invokeArgs($service, [['host' => 'database', 'database' => 'laravel'], 'laravel', &$credentialPath]);

        expect($command[1])->toStartWith('--defaults-file=')
            ->and(file_get_contents($credentialPath))->not->toContain('database=');
    } finally {
        if (is_string($credentialPath) && is_file($credentialPath)) {
            unlink($credentialPath);
        }
    }
});
