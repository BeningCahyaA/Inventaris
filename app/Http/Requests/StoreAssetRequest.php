<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category' => 'required|exists:categories,slug',
            'status' => 'required|in:in_use,maintenance,broken,available',
            'purchase_date' => 'required|date',
            'condition' => 'required|in:baik,rusak,rusak_total',
            'price' => 'nullable|numeric|min:0',
            'description' => 'required|string',
            'person_in_charge' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'warranty_expiry_date' => 'nullable|date',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:5120',
        ];
    }
}
