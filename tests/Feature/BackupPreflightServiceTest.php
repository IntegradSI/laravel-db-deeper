<?php

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Integrad\DbDeeper\Services\BackupEncryptionKeyService;
use Integrad\DbDeeper\Services\BackupPreflightService;

it('passes the preflight when the key, database, dump executable and destination are available', function () {
    config()->set('database.connections.preflight-test.driver', 'pgsql');
    config()->set('db-deeper.backup.pg_dump_binary', PHP_BINARY);

    $this->mock(BackupEncryptionKeyService::class)
        ->shouldReceive('active')
        ->once()
        ->andReturn([
            'key_id' => 1,
            'secret' => str_repeat('x', 32),
            'fingerprint' => str_repeat('a', 64),
            'source' => 'dashboard',
        ]);

    $databaseConnection = Mockery::mock();
    $databaseConnection->shouldReceive('getPdo')->once()->andReturn(new PDO('sqlite::memory:'));
    DB::shouldReceive('connection')->once()->with('preflight-test')->andReturn($databaseConnection);

    $disk = Mockery::mock(Filesystem::class);
    $disk->shouldReceive('put')->once()->withArgs(static fn (string $path, string $contents): bool => str_contains($path, '.db-deeper-diagnostic-') && strlen($contents) === 32)->andReturnTrue();
    $disk->shouldReceive('delete')->once()->withArgs(static fn (string $path): bool => str_contains($path, '.db-deeper-diagnostic-'))->andReturnTrue();

    $filesystem = Mockery::mock(FilesystemFactory::class);
    $filesystem->shouldReceive('disk')->once()->with('backup-test')->andReturn($disk);

    $result = (new BackupPreflightService($filesystem, app(BackupEncryptionKeyService::class)))
        ->diagnose('preflight-test', 'backup-test', 'database-backups');

    expect($result['ready'])->toBeTrue()
        ->and($result['checks'])->toHaveCount(4)
        ->and(collect($result['checks'])->pluck('status')->unique()->all())->toBe(['passed']);
});

it('reports safe instructions when the preflight checks fail', function () {
    config()->set('database.connections.preflight-test.driver', 'mysql');
    config()->set('db-deeper.backup.mysql_dump_binary', 'db-deeper-missing-dump-command');

    $this->mock(BackupEncryptionKeyService::class)
        ->shouldReceive('active')
        ->once()
        ->andThrow(new RuntimeException('secret data must not be returned'));

    $databaseConnection = Mockery::mock();
    $databaseConnection->shouldReceive('getPdo')->once()->andThrow(new RuntimeException('database password must not be returned'));
    DB::shouldReceive('connection')->once()->with('preflight-test')->andReturn($databaseConnection);

    $disk = Mockery::mock(Filesystem::class);
    $disk->shouldReceive('put')->once()->andReturnFalse();
    $filesystem = Mockery::mock(FilesystemFactory::class);
    $filesystem->shouldReceive('disk')->once()->with('backup-test')->andReturn($disk);

    $result = (new BackupPreflightService($filesystem, app(BackupEncryptionKeyService::class)))
        ->diagnose('preflight-test', 'backup-test', 'database-backups');

    expect($result['ready'])->toBeFalse()
        ->and(collect($result['checks'])->pluck('status')->unique()->all())->toBe(['failed'])
        ->and(json_encode($result))->not->toContain('secret data', 'password must not be returned');
});
