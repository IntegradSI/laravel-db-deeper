<?php

namespace Integrad\DbDeeper\Services;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Integrad\DbDeeper\Jobs\RunDatabaseBackup;
use Integrad\DbDeeper\Models\Backup;
use RuntimeException;
use Throwable;

class BackupRunService
{
    public function __construct(private Dispatcher $dispatcher, private DatabaseBackupService $databaseBackups) {}

    public function queue(string $connectionName, string $diskName, ?string $path = null, ?string $requestedBy = null, ?int $scheduleId = null): Backup
    {
        $connectionConfig = config("database.connections.{$connectionName}");
        $driver = is_array($connectionConfig) ? (string) ($connectionConfig['driver'] ?? '') : '';

        if (! in_array($driver, ['mysql', 'mariadb', 'pgsql'], true)) {
            throw new RuntimeException("A conexão [{$connectionName}] não possui um driver de backup disponível.");
        }

        $backup = Backup::query()->create([
            'schedule_id' => $scheduleId,
            'requested_by' => $requestedBy,
            'connection_name' => $connectionName,
            'driver' => $driver,
            'disk_name' => $diskName,
            'destination_path' => $path,
            'status' => Backup::STATUS_QUEUED,
            'queued_at' => now(),
        ]);

        try {
            $this->dispatcher->dispatch(new RunDatabaseBackup($backup->id));
        } catch (Throwable $exception) {
            $backup->forceFill([
                'status' => Backup::STATUS_FAILED,
                'error_message' => 'A fila não aceitou a execução. Confira a conexão de fila e o worker do Laravel.',
                'completed_at' => now(),
            ])->save();

            Log::warning('DB Deeper could not dispatch a backup run.', [
                'backup_id' => $backup->id,
                'exception' => $exception::class,
            ]);

            throw $exception;
        }

        return $backup->refresh();
    }

    public function execute(int $backupId): void
    {
        $claimed = DB::transaction(function () use ($backupId): bool {
            $backup = Backup::query()->whereKey($backupId)->lockForUpdate()->firstOrFail();

            if ($backup->status !== Backup::STATUS_QUEUED) {
                return false;
            }

            $backup->forceFill([
                'status' => Backup::STATUS_RUNNING,
                'started_at' => now(),
            ])->save();

            return true;
        });

        if (! $claimed) {
            return;
        }

        $backup = Backup::query()->findOrFail($backupId);

        try {
            $result = $this->databaseBackups->create(
                $backup->connection_name,
                $backup->disk_name,
                $backup->destination_path,
            );

            $backup->forceFill([
                'status' => Backup::STATUS_SUCCEEDED,
                'path' => $result['path'],
                'size_bytes' => $result['bytes'],
                'checksum' => $result['checksum'],
                'encryption_key_id' => $result['key_id'],
                'encryption_key_fingerprint' => $result['key_fingerprint'],
                'completed_at' => now(),
                'error_message' => null,
            ])->save();
        } catch (Throwable $exception) {
            $backup->forceFill([
                'status' => Backup::STATUS_FAILED,
                'error_message' => 'O dump não foi concluído. Confira a ferramenta mysqldump/pg_dump, o acesso ao banco, a chave ativa e a permissão de gravação no disco.',
                'completed_at' => now(),
            ])->save();

            Log::warning('DB Deeper backup run failed.', [
                'backup_id' => $backup->id,
                'exception' => $exception::class,
            ]);

            throw $exception;
        }
    }
}
