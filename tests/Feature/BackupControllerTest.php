<?php

use Integrad\DbDeeper\Models\Backup;
use Integrad\DbDeeper\Services\BackupRunService;

it('opens the execution history after queuing a manual backup', function () {
    config()->set('database.connections.mysql.driver', 'mysql');
    config()->set('filesystems.disks.backup-test.driver', 'local');

    $backup = new Backup;
    $backup->id = 42;

    $this->mock(BackupRunService::class)
        ->shouldReceive('queue')
        ->once()
        ->with('mysql', 'backup-test', 'database-backups', '')
        ->andReturn($backup);

    $this->withoutMiddleware()
        ->post('/db-deeper/backups/run', [
            'connection_name' => 'mysql',
            'disk_name' => 'backup-test',
            'destination_path' => 'database-backups',
        ])
        ->assertRedirect(route('db-deeper.backups.index', ['tab' => 'history']))
        ->assertSessionHas('success', 'Execução #42 enviada para processamento.');
});
