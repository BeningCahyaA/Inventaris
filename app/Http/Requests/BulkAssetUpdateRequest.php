<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkAssetUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'super_admin'], true);
    }

    public function rules(): array
    {
        return [
            'asset_ids'        => ['required', 'array', 'min:1'],
            'asset_ids.*'      => ['integer', 'exists:assets,id'],
            'status'           => ['nullable', 'string', 'in:available,in_use,borrowed,maintenance,broken'],
            'borrower_name' => ['exclude_unless:status,in_use,borrowed', 'required', 'string', 'max:255'],
            'due_at' => ['exclude_unless:status,in_use,borrowed', 'nullable', 'date'],
            'category'         => ['nullable', 'string', 'exists:categories,slug'],
            'person_in_charge' => ['nullable', 'string', 'max:255'],
            'location'         => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'asset_ids.required' => 'Pilih minimal satu aset.',
            'asset_ids.min'      => 'Pilih minimal satu aset.',
            'asset_ids.*.exists' => 'Satu atau lebih ID aset tidak valid.',
        ];
    }

    /**
     * Hanya ambil field yang benar-benar diisi (partial update).
     */
    public function onlyFilled(): array
    {
        $fields = ['status', 'category', 'person_in_charge', 'location'];

        return collect($fields)
            ->filter(fn ($f) => $this->filled($f))
            ->mapWithKeys(fn ($f) => [$f => $this->input($f)])
            ->toArray();
    }
}
