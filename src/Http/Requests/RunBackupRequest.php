<?php

namespace Integrad\DbDeeper\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RunBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $connections = array_filter(
            (array) config('database.connections', []),
            static fn (array $connection): bool => in_array($connection['driver'] ?? null, ['mysql', 'mariadb', 'pgsql'], true),
        );

        return [
            'connection_name' => ['required', 'string', Rule::in(array_keys($connections))],
            'disk_name' => ['required', 'string', Rule::in(array_keys((array) config('filesystems.disks', [])))],
            'destination_path' => ['nullable', 'string', 'max:500', 'regex:/^[A-Za-z0-9._\/-]*$/', 'not_regex:/\.\./'],
        ];
    }
}
