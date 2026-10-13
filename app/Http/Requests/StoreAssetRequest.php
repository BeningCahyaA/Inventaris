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
            'status' => 'required|in:in_use,maintenance,broken,available,sold',
            'sale_date' => 'exclude_unless:status,sold|required|date_format:Y-m-d|before_or_equal:'.now('Asia/Jakarta')->toDateString().'|after_or_equal:purchase_date',
            'sale_price' => 'exclude_unless:status,sold|required|numeric|min:0',
            'sale_buyer' => 'exclude_unless:status,sold|nullable|string|max:255',
            'sale_notes' => 'exclude_unless:status,sold|nullable|string|max:2000',
            'sale_proof' => 'exclude_unless:status,sold|required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'purchase_date' => 'required|date',
            'condition' => 'required|in:baik,rusak,rusak_total',
            'price' => 'nullable|numeric|min:0',
            'depreciation_rate' => 'nullable|numeric|min:0|max:100',
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
