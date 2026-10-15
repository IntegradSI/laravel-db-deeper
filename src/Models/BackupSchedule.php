<?php

namespace Integrad\DbDeeper\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BackupSchedule extends Model
{
    protected $table = 'db_deeper_backup_schedules';

    protected $fillable = [
        'name',
        'is_enabled',
        'frequency',
        'run_at',
        'day_of_week',
        'day_of_month',
        'timezone',
        'connection_name',
        'disk_name',
        'destination_path',
        'next_run_at',
        'last_run_at',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'day_of_week' => 'integer',
            'day_of_month' => 'integer',
            'next_run_at' => 'datetime',
            'last_run_at' => 'datetime',
        ];
    }

    public function backups(): HasMany
    {
        return $this->hasMany(Backup::class, 'schedule_id');
    }
}
