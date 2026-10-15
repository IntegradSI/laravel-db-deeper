<?php

namespace Integrad\DbDeeper\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBackupScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewDbDeeper') ?? false;
    }

    public function rules(): array
    {
        $connections = array_filter(
            (array) config('database.connections', []),
            static fn (array $connection): bool => in_array($connection['driver'] ?? null, ['mysql', 'mariadb', 'pgsql'], true),
        );

        return [
            'name' => ['required', 'string', 'max:120'],
            'is_enabled' => ['required', 'boolean'],
            'frequency' => ['required', Rule::in(['hourly', 'daily', 'weekly', 'monthly'])],
            'run_at' => ['nullable', 'required_unless:frequency,hourly', 'date_format:H:i'],
            'day_of_week' => ['nullable', 'required_if:frequency,weekly', 'integer', 'between:0,6'],
            'day_of_month' => ['nullable', 'required_if:frequency,monthly', 'integer', 'between:1,28'],
            'timezone' => ['required', 'timezone'],
            'connection_name' => ['required', 'string', Rule::in(array_keys($connections))],
            'disk_name' => ['required', 'string', Rule::in(array_keys((array) config('filesystems.disks', [])))],
            'destination_path' => ['nullable', 'string', 'max:500', 'regex:/^[A-Za-z0-9._\/-]*$/', 'not_regex:/\.\./'],
        ];
    }
}
