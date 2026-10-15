<?php

namespace Integrad\DbDeeper\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBackupKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewDbDeeper') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
        ];
    }
}
