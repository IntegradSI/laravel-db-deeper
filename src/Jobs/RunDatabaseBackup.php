<?php

namespace Integrad\DbDeeper\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Integrad\DbDeeper\Services\BackupRunService;

class RunDatabaseBackup implements ShouldQueue
{
    public int $tries = 1;

    public int $timeout = 3600;

    public function __construct(public int $backupId) {}

    public function handle(BackupRunService $backupRuns): void
    {
        $backupRuns->execute($this->backupId);
    }
}
