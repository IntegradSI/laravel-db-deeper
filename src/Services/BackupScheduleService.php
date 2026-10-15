<?php

namespace Integrad\DbDeeper\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Integrad\DbDeeper\Models\BackupSchedule;
use Throwable;

class BackupScheduleService
{
    public function __construct(
        private BackupScheduleFrequency $frequency,
        private BackupRunService $backupRuns,
    ) {}

    /** @param array<string, mixed> $values */
    public function create(array $values): BackupSchedule
    {
        $values['next_run_at'] = $values['is_enabled']
            ? $this->frequency->nextRun($values)
            : null;

        return BackupSchedule::query()->create($values);
    }

    /** @param array<string, mixed> $values */
    public function update(BackupSchedule $schedule, array $values): BackupSchedule
    {
        $values['next_run_at'] = $values['is_enabled']
            ? $this->frequency->nextRun($values)
            : null;

        $schedule->update($values);

        return $schedule->refresh();
    }

    public function toggle(BackupSchedule $schedule): BackupSchedule
    {
        $schedule->is_enabled = ! $schedule->is_enabled;
        $schedule->next_run_at = $schedule->is_enabled
            ? $this->frequency->nextRun($schedule->toArray())
            : null;
        $schedule->save();

        return $schedule->refresh();
    }

    public function queueDue(): int
    {
        $now = CarbonImmutable::now('UTC');
        $scheduleIds = BackupSchedule::query()
            ->where('is_enabled', true)
            ->where('next_run_at', '<=', $now)
            ->orderBy('id')
            ->limit(100)
            ->pluck('id');
        $queuedCount = 0;

        foreach ($scheduleIds as $scheduleId) {
            $scheduleData = DB::transaction(function () use ($scheduleId, $now): ?array {
                $schedule = BackupSchedule::query()->whereKey($scheduleId)->lockForUpdate()->first();

                if (! $schedule || ! $schedule->is_enabled || ! $schedule->next_run_at?->lessThanOrEqualTo($now)) {
                    return null;
                }

                $values = $schedule->only(['frequency', 'timezone', 'run_at', 'day_of_week', 'day_of_month']);
                $schedule->forceFill([
                    'last_run_at' => $now,
                    'next_run_at' => $this->frequency->nextRun($values, $now),
                ])->save();

                return [
                    'id' => $schedule->id,
                    'connection_name' => $schedule->connection_name,
                    'disk_name' => $schedule->disk_name,
                    'destination_path' => $schedule->destination_path,
                ];
            });

            if ($scheduleData === null) {
                continue;
            }

            try {
                $this->backupRuns->queue(
                    $scheduleData['connection_name'],
                    $scheduleData['disk_name'],
                    $scheduleData['destination_path'],
                    null,
                    $scheduleData['id'],
                );
                $queuedCount++;
            } catch (Throwable $exception) {
                Log::warning('DB Deeper could not queue a scheduled backup.', [
                    'schedule_id' => $scheduleData['id'],
                    'exception' => $exception::class,
                ]);
            }
        }

        return $queuedCount;
    }
}
