<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScannerStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->user()?->role;

        return in_array($role, ['admin', 'super_admin'], true);
    }

    public function rules(): array
    {
        return [
            'asset_tag'     => ['required', 'string', 'exists:assets,asset_tag'],
            'action'        => ['required', 'string', 'in:check_out,check_in,maintenance'],
            'borrower_name' => ['nullable', 'string', 'max:255'],
            'due_at'        => ['nullable', 'date', 'after:today'],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'asset_tag.exists' => 'QR tidak dikenali: aset dengan kode ini tidak ditemukan.',
            'action.in'        => 'Aksi tidak valid. Pilih check_out, check_in, atau maintenance.',
            'due_at.after'     => 'Tanggal jatuh tempo harus setelah hari ini.',
        ];
    }
}
