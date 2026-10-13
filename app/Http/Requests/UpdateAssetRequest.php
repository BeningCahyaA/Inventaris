<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
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
            'description' => 'required|string',
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
            'person_in_charge' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'warranty_expiry_date' => 'nullable|date',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:5120',
            'borrower_name' => 'required_if:status,in_use|nullable|string|max:255',
            'due_at' => 'nullable|date',
            'borrow_notes' => 'nullable|string|max:500',
            'delete_attachments' => 'nullable|array',
            'delete_attachments.*' => [
                'integer',
                Rule::exists('asset_attachments', 'id')->where('asset_id', $this->route('id')),
            ],
        ];
    }

    public function after(): array
    {
        return [function ($validator) {
            if ($this->input('status') !== 'sold' || $validator->errors()->has('sale_date')) return;
            $latest = \App\Models\AssetDepreciation::where('asset_id', $this->route('id'))->max('depreciated_on');
            if ($latest && $this->input('sale_date') < $latest) {
                $validator->errors()->add('sale_date', 'Tanggal penjualan tidak boleh mendahului riwayat penyusutan terakhir ('.$latest.').');
            }
        }];
    }
}
