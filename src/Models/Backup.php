<?php

namespace Integrad\DbDeeper\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Backup extends Model
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_RUNNING = 'running';

    public const STATUS_SUCCEEDED = 'succeeded';

    public const STATUS_FAILED = 'failed';

    protected $table = 'db_deeper_backups';

    protected $fillable = [
        'schedule_id',
        'encryption_key_id',
        'requested_by',
        'connection_name',
        'driver',
        'disk_name',
        'destination_path',
        'path',
        'status',
        'size_bytes',
        'checksum',
        'encryption_key_fingerprint',
        'error_message',
        'queued_at',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'queued_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(BackupSchedule::class, 'schedule_id');
    }

    public function encryptionKey(): BelongsTo
    {
        return $this->belongsTo(BackupKey::class, 'encryption_key_id');
    }
}
