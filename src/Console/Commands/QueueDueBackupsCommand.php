<?php

namespace Integrad\DbDeeper\Console\Commands;

use Illuminate\Console\Command;
use Integrad\DbDeeper\Services\BackupScheduleService;

class QueueDueBackupsCommand extends Command
{
    protected $signature = 'db-deeper:queue-due-backups';

    protected $description = 'Enfileira as rotinas de backup que atingiram o próximo horário.';

    public function handle(BackupScheduleService $backupSchedules): int
    {
        $queuedCount = $backupSchedules->queueDue();

        if ($queuedCount > 0) {
            $this->components->info("{$queuedCount} rotina(s) de backup enfileirada(s).");
        }

        return self::SUCCESS;
    }
}
