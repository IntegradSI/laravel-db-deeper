<?php

namespace Integrad\DbDeeper\Models;

use Illuminate\Database\Eloquent\Model;

class BackupKey extends Model
{
    protected $table = 'db_deeper_backup_keys';

    protected $fillable = [
        'name',
        'secret',
        'fingerprint',
        'is_active',
        'activated_at',
    ];

    protected $hidden = ['secret'];

    protected function casts(): array
    {
        return [
            'secret' => 'encrypted',
            'is_active' => 'boolean',
            'activated_at' => 'datetime',
        ];
    }
}
