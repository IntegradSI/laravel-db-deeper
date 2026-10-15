<?php

namespace Integrad\DbDeeper\Services;

use Integrad\DbDeeper\Models\Backup;

class BackupHistoryService
{
    /**
     * @return array{queuedCount: int, succeededCount: int, failedCount: int, backups: mixed}
     */
    public function snapshot(): array
    {
        return [
            'queuedCount' => Backup::query()->whereIn('status', [Backup::STATUS_QUEUED, Backup::STATUS_RUNNING])->count(),
            'succeededCount' => Backup::query()->where('status', Backup::STATUS_SUCCEEDED)->count(),
            'failedCount' => Backup::query()->where('status', Backup::STATUS_FAILED)->count(),
            'backups' => Backup::query()->with(['schedule:id,name'])->latest()->paginate(20),
        ];
    }
}
