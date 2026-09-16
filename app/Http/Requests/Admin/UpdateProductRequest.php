<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'cashier']);
    }

    public function rules(): array
    {
        $productId = $this->route('product')->id;

        return [
            'name'                => ['required', 'string', 'min:2', 'max:200'],
            'category_id'         => ['nullable', 'exists:categories,id'],
            'supplier_id'         => ['nullable', 'exists:suppliers,id'],
            'sku'                 => ['required', 'string', 'max:100',
                                      Rule::unique('products', 'sku')->ignore($productId)->whereNull('deleted_at')],
            'barcode'             => ['nullable', 'string', 'max:100',
                                      Rule::unique('products', 'barcode')->ignore($productId)->whereNull('deleted_at')],
            'description'         => ['nullable', 'string', 'max:1000'],
            'unit'                => ['required', 'string', 'in:pc,kg,meter,box,liter,pair,set,roll,sheet,bag'],
            'cost_price'          => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'sale_price'          => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'stock_quantity'      => [auth()->user()?->isAdmin() ? 'required' : 'nullable', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'image'               => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
            'remove_image'        => ['nullable', 'boolean'],
            'show_in_catalog'     => ['nullable', 'boolean'],
            'is_active'           => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'       => 'Product name is required.',
            'sku.required'        => 'SKU is required.',
            'sku.unique'          => 'This SKU is already taken by another product.',
            'barcode.unique'      => 'This barcode is already assigned to another product.',
            'unit.in'             => 'Please select a valid unit.',
            'cost_price.required' => 'Cost price is required.',
            'sale_price.required' => 'Sale price is required.',
            'image.max'           => 'Image must be under 2MB.',
        ];
    }
}
