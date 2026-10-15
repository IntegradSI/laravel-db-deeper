<?php

namespace Integrad\DbDeeper\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\View\Factory as ViewFactory;
use Integrad\DbDeeper\Models\BackupSchedule;
use Integrad\DbDeeper\Services\BackupEncryptionKeyService;
use Integrad\DbDeeper\Services\BackupHistoryService;
use Integrad\DbDeeper\Services\BackupScheduleFrequency;

class BackupManagementController
{
    public function __invoke(
        ViewFactory $views,
        BackupEncryptionKeyService $keys,
        BackupScheduleFrequency $frequency,
        BackupHistoryService $history,
    ): View {
        $connections = array_filter(
            (array) config('database.connections', []),
            static fn (array $connection): bool => in_array($connection['driver'] ?? null, ['mysql', 'mariadb', 'pgsql'], true),
        );

        return $views->make('db-deeper::backups', [
            'activePage' => 'backups',
            'pageTitle' => 'Backups',
            'autoRefresh' => true,
            'liveRefreshUrl' => route('db-deeper.live.backups'),
            'refreshInterval' => max(30, (int) config('db-deeper.dashboard.refresh_interval_seconds', 30)),
            'databaseConnection' => config('db-deeper.backup.connection') ?: config('database.default'),
            'connections' => $connections,
            'disks' => array_keys((array) config('filesystems.disks', [])),
            'schedules' => BackupSchedule::query()->orderBy('name')->get(),
            'keyStatus' => $keys->currentStatus(),
            'frequencyService' => $frequency,
            ...$history->snapshot(),
        ]);
    }
}
