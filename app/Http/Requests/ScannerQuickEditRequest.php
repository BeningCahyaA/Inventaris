<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScannerQuickEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_id' => 'required|exists:assets,id',
            'name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'person_in_charge' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'condition' => 'nullable|in:baik,rusak,rusak_total',
            'description' => 'nullable|string',
        ];
    }
}
