<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'cashier']);
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'min:2', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon'        => ['nullable', 'string', 'max:50'],
            'is_active'   => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'name.unique'   => 'A category with this name already exists.',
            'name.min'      => 'Category name must be at least 2 characters.',
        ];
    }
}
