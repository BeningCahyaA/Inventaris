<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'depreciation_rate' => 'sometimes|required|numeric|min:0|max:100',
            'name' => 'required|string|max:100|unique:categories,name',
            'slug' => 'required|string|max:50|unique:categories,slug|alpha_dash',
            'prefix' => 'required|string|max:5|unique:categories,prefix',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Nama kategori sudah digunakan.',
            'slug.unique' => 'Slug kategori sudah digunakan.',
            'slug.alpha_dash' => 'Slug hanya boleh huruf, angka, dash, dan underscore.',
            'prefix.unique' => 'Prefix sudah digunakan kategori lain.',
        ];
    }
}
