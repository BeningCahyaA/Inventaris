<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('category');

        return [
            'name' => "required|string|max:100|unique:categories,name,{$id}",
            'slug' => "required|string|max:50|unique:categories,slug,{$id}|alpha_dash",
            'prefix' => "required|string|max:5|unique:categories,prefix,{$id}",
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
