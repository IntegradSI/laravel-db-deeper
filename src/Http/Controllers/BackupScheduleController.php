<?php

namespace Integrad\DbDeeper\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\Factory as ViewFactory;
use Integrad\DbDeeper\Http\Requests\StoreBackupScheduleRequest;
use Integrad\DbDeeper\Models\BackupSchedule;
use Integrad\DbDeeper\Services\BackupScheduleService;

class BackupScheduleController
{
    public function edit(BackupSchedule $schedule, ViewFactory $views): View
    {
        $connections = array_filter(
            (array) config('database.connections', []),
            static fn (array $connection): bool => in_array($connection['driver'] ?? null, ['mysql', 'mariadb', 'pgsql'], true),
        );

        return $views->make('db-deeper::backup-schedule-edit', [
            'activePage' => 'backups',
            'pageTitle' => 'Editar rotina de backup',
            'autoRefresh' => false,
            'liveRefreshUrl' => '',
            'refreshInterval' => 30,
            'databaseConnection' => config('db-deeper.backup.connection') ?: config('database.default'),
            'schedule' => $schedule,
            'connections' => $connections,
            'disks' => array_keys((array) config('filesystems.disks', [])),
        ]);
    }

    public function store(StoreBackupScheduleRequest $request, BackupScheduleService $schedules): RedirectResponse
    {
        $schedules->create($request->validated());

        return back()->with('success', 'Rotina de backup criada.');
    }

    public function update(StoreBackupScheduleRequest $request, BackupSchedule $schedule, BackupScheduleService $schedules): RedirectResponse
    {
        $schedules->update($schedule, $request->validated());

        return back()->with('success', 'Rotina de backup atualizada.');
    }

    public function toggle(BackupSchedule $schedule, BackupScheduleService $schedules): RedirectResponse
    {
        $schedules->toggle($schedule);

        return back()->with('success', $schedule->is_enabled ? 'Rotina reativada.' : 'Rotina pausada.');
    }

    public function destroy(BackupSchedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return back()->with('success', 'Rotina removida. O histórico de backups foi preservado.');
    }
}
